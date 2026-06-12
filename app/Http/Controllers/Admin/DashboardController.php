<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PartNg;
use App\Models\Pricelist;
use App\Services\ExcelExportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Cache priceMap agar tidak query Pricelist::all() berulang kali
     * dalam satu request maupun antar request (TTL 10 menit).
     */
    private function getPriceMap(): array
    {
        return Cache::remember('pricelist_map', 600, function () {
            $map = [];
            foreach (Pricelist::all() as $p) {
                if ($p->kode_part) $map[$p->kode_part] = $p->harga_usd;
                if ($p->no_rak)    $map[$p->no_rak]    = $p->harga_usd;
            }
            return $map;
        });
    }

    /**
     * Terapkan filter dari request ke query builder.
     * Validasi format input dilakukan sebelum dipakai Carbon
     * agar tidak throw exception jika input tidak valid.
     */
    private function applyFilters($query, Request $request)
    {
        // Filter tanggal spesifik
        if ($request->filled('date') && $this->isValidDate($request->date)) {
            $query->whereDate('Date_Part_Ng', $request->date);
        }

        // Filter bulan
        if ($request->filled('month') && $this->isValidYearMonth($request->month)) {
            $date = Carbon::createFromFormat('Y-m', $request->month);
            $query->whereYear('Date_Part_Ng', $date->year)
                  ->whereMonth('Date_Part_Ng', $date->month);
        }

        // Filter kategori
        if ($request->filled('category')) {
            if ($request->category === 'bukan tanggung jawab') {
                $query->where('Category_Part_Ng', 'like', 'bukan tanggung jawab%');
            } else {
                $query->where('Category_Part_Ng', $request->category);
            }
        }

        // Filter divisi
        if ($request->filled('divisi')) {
            $query->where('Divisi', $request->divisi);
        }

        // Filter minggu — hanya dihitung jika belum ada filter date/month
        // yang sudah membatasi rentang yang sama, untuk menghindari double-filter.
        if ($request->filled('week') && !$request->filled('date')) {
            $this->applyWeekFilter($query, $request);
        }

        return $query;
    }

    /**
     * Pisahkan logika filter minggu agar lebih mudah diuji dan dibaca.
     * Edge case bulan pendek ditangani dengan Carbon yang akurat.
     */
    private function applyWeekFilter($query, Request $request): void
    {
        if ($request->filled('month') && $this->isValidYearMonth($request->month)) {
            $h = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
        } else {
            $h = Carbon::today()->startOfMonth();
        }

        $weekNum = max(1, (int) $request->week);

        // Hitung start dan end hari di bulan tersebut
        $startDay = ($weekNum - 1) * 7 + 1;
        $endDay   = min($weekNum * 7, $h->daysInMonth);

        // Jangan query jika minggu melebihi jumlah hari di bulan
        if ($startDay > $h->daysInMonth) {
            return;
        }

        $query->whereYear('Date_Part_Ng', $h->year)
              ->whereMonth('Date_Part_Ng', $h->month)
              ->whereDay('Date_Part_Ng', '>=', $startDay)
              ->whereDay('Date_Part_Ng', '<=', $endDay);
    }

    /**
     * Data chart total Part NG per hari dalam bulan.
     * Filter bulan di-apply di sini sebagai "base scope";
     * applyFilters hanya menambahkan filter tambahan (category, divisi, week)
     * tanpa mengulang filter bulan.
     */
    private function getTotalChartData(Carbon $month, Request $request): array
    {
        $query = PartNg::query()
            ->whereYear('Date_Part_Ng', $month->year)
            ->whereMonth('Date_Part_Ng', $month->month);

        // Hanya terapkan filter category, divisi, week — bukan date/month
        // (sudah di-handle oleh base scope di atas)
        $this->applyNonDateFilters($query, $request);

        $raw = $query->select(
                DB::raw('DATE(Date_Part_Ng) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw('DATE(Date_Part_Ng)'))
            ->orderBy('date', 'asc')
            ->get();

        $dates  = $raw->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d M'));
        $totals = $raw->pluck('total');

        return [$dates, $totals];
    }

    /**
     * Hitung total Part NG yang sudah/belum diproses dalam satu bulan.
     * Menggunakan dua query SELECT SUM(...) agar tidak load semua baris ke PHP.
     */
    private function getProcessedComparison(Carbon $month, Request $request): array
    {
        $base = PartNg::query()
            ->whereYear('Date_Part_Ng', $month->year)
            ->whereMonth('Date_Part_Ng', $month->month);

        $this->applyNonDateFilters($base, $request);

        // Clone setelah semua filter diterapkan agar keduanya identik
        $processed = (clone $base)
            ->whereNotNull('penanggungjawab')
            ->where('penanggungjawab', '!=', '')
            ->count();

        $unprocessed = (clone $base)
            ->where(function ($q) {
                $q->whereNull('penanggungjawab')->orWhere('penanggungjawab', '');
            })
            ->count();

        return [$processed, $unprocessed];
    }

    /**
     * Data chart cost harian.
     * Kalkulasi cost dilakukan di PHP karena JOIN ke Pricelist
     * membutuhkan logika lookup dua kolom (Code_Item_Rack / Code_Rack).
     * Namun data yang di-load sudah dibatasi per bulan dan hanya kolom yang diperlukan.
     */
    private function getCostChartData(Carbon $month, Request $request): array
    {
        $query = PartNg::query()
            ->whereYear('Date_Part_Ng', $month->year)
            ->whereMonth('Date_Part_Ng', $month->month)
            ->select('Date_Part_Ng', 'Code_Item_Rack', 'Code_Rack', 'Total_Part_Ng', 'harga_snapshot');

        $this->applyNonDateFilters($query, $request);

        $parts    = $query->get();
        $priceMap = $this->getPriceMap();

        $daily = [];
        foreach ($parts as $part) {
            $date  = Carbon::parse($part->Date_Part_Ng)->format('Y-m-d');
            // Prioritas: snapshot harga saat input, fallback ke pricelist terkini
            if ($part->harga_snapshot !== null) {
                $harga = (float) $part->harga_snapshot;
            } else {
                $harga = $priceMap[$part->Code_Item_Rack] ?? $priceMap[$part->Code_Rack] ?? 0;
            }
            $daily[$date] = ($daily[$date] ?? 0) + ($harga * $part->Total_Part_Ng);
        }

        $dates  = [];
        $totals = [];
        $start  = $month->copy()->startOfMonth();
        $end    = $month->copy()->endOfMonth();

        foreach ($start->daysUntil($end) as $day) {
            $d        = $day->format('Y-m-d');
            $dates[]  = $day->format('d M');
            $totals[] = $daily[$d] ?? 0;
        }

        return [$dates, $totals, (float) array_sum($totals)];
    }

    /**
     * Terapkan hanya filter non-tanggal (category, divisi, week).
     * Digunakan oleh method chart yang sudah memiliki base scope bulan sendiri,
     * sehingga filter date/month dari request tidak diterapkan dua kali.
     */
    private function applyNonDateFilters($query, Request $request): void
    {
        if ($request->filled('category')) {
            if ($request->category === 'bukan tanggung jawab') {
                $query->where('Category_Part_Ng', 'like', 'bukan tanggung jawab%');
            } else {
                $query->where('Category_Part_Ng', $request->category);
            }
        }

        if ($request->filled('divisi')) {
            $query->where('Divisi', $request->divisi);
        }

        if ($request->filled('week')) {
            $this->applyWeekFilter($query, $request);
        }
    }

    // -------------------------------------------------------------------------
    // Public actions
    // -------------------------------------------------------------------------

    public function dashboard(Request $request)
    {
        $selectedMonth = $request->filled('month') && $this->isValidYearMonth($request->month)
            ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()
            : Carbon::now()->startOfMonth();

        [$chartDates, $chartTotals]           = $this->getTotalChartData($selectedMonth, $request);
        [$processedTotal, $unprocessedTotal]  = $this->getProcessedComparison($selectedMonth, $request);
        [$costDates, $costTotals, $totalCost] = $this->getCostChartData($selectedMonth, $request);

        $prevMonth  = $selectedMonth->copy()->subMonth()->format('Y-m');
        $nextMonth  = $selectedMonth->copy()->addMonth()->format('Y-m');
        $monthLabel = $selectedMonth->translatedFormat('F Y');

        return view('admin.dashboard', compact(
            'chartDates', 'chartTotals',
            'processedTotal', 'unprocessedTotal',
            'costDates', 'costTotals', 'totalCost',
            'selectedMonth', 'prevMonth', 'nextMonth', 'monthLabel'
        ));
    }

    /**
     * Hitung cost dan lampirkan ke setiap part sebagai properti sementara.
     * Prioritas: harga_snapshot (dilock saat input) → fallback pricelist terkini.
     */
    private function attachCost($parts): float
    {
        $priceMap  = $this->getPriceMap();
        $totalCost = 0.0;

        foreach ($parts as $part) {
            if ($part->harga_snapshot !== null) {
                $harga = (float) $part->harga_snapshot;
            } else {
                $harga = $priceMap[$part->Code_Item_Rack] ?? $priceMap[$part->Code_Rack] ?? 0;
            }
            $part->harga_satuan = $harga;
            $part->cost         = $harga * $part->Total_Part_Ng;
            $totalCost         += $part->cost;
        }

        return $totalCost;
    }

    /**
     * Method helper terpadu untuk semua laporan.
     * Menggantikan 6 method terpisah (report, reportUnprocessed, reportProcessed,
     * reportUnprocessedMonthly, reportProcessedMonthly) yang sebelumnya redundan.
     *
     * @param string      $view      Nama blade view
     * @param string|null $status    'processed' | 'unprocessed' | null (semua)
     * @param bool        $monthly   true = default ke bulan ini, false = default ke hari ini
     */
    private function buildReport(Request $request, string $view, ?string $status, bool $monthly): \Illuminate\View\View
    {
        $query = PartNg::with('member');
        $query = $this->applyFilters($query, $request);

        // Tentukan default scope & selectedMonth
        $selectedMonth = Carbon::now();
        if (!$request->filled('date') && !$request->filled('month')) {
            if ($monthly) {
                $query->whereYear('Date_Part_Ng', $selectedMonth->year)
                      ->whereMonth('Date_Part_Ng', $selectedMonth->month);
            } else {
                $query->whereDate('Date_Part_Ng', Carbon::today());
            }
        }

        // Terapkan filter status processed/unprocessed
        if ($status === 'processed') {
            $query->whereNotNull('penanggungjawab')->where('penanggungjawab', '!=', '');
        } elseif ($status === 'unprocessed') {
            $query->where(function ($q) {
                $q->whereNull('penanggungjawab')->orWhere('penanggungjawab', '');
            });
        }

        $parts     = $query->orderBy('Date_Part_Ng', 'desc')->get();
        $totalCost = $this->attachCost($parts);

        $prevMonth  = $selectedMonth->copy()->subMonth()->format('Y-m');
        $nextMonth  = $selectedMonth->copy()->addMonth()->format('Y-m');
        $monthLabel = $selectedMonth->translatedFormat('F Y');

        return view($view, compact('parts', 'totalCost', 'prevMonth', 'nextMonth', 'monthLabel'));
    }

    public function report(Request $request)
    {
        return $this->buildReport($request, 'admin.report', null, false);
    }

    public function reportUnprocessed(Request $request)
    {
        return $this->buildReport($request, 'admin.report-unprocessed', 'unprocessed', true);
    }

    public function reportProcessed(Request $request)
    {
        return $this->buildReport($request, 'admin.report-processed', 'processed', true);
    }

    public function process(Request $request, $id)
    {
        $request->validate([
            'penyebab'       => 'required|string|max:255',
            'penanganan'     => 'required|string|max:255',
            'penanggungjawab' => 'nullable|string|max:255',
        ]);

        $part = PartNg::findOrFail($id);
        $part->update([
            'penanggungjawab' => $request->filled('penanggungjawab') ? $request->penanggungjawab : $part->penanggungjawab,
            'penyebab'   => $request->penyebab,
            'penanganan' => $request->penanganan,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data Part NG berhasil diproses.']);
        }

        return back()->with('success', 'Data Part NG berhasil diproses.');
    }

    public function ranking(Request $request)
    {
        $selectedMonth = $request->filled('month') && $this->isValidYearMonth($request->month)
            ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $query = PartNg::query()
            ->whereYear('Date_Part_Ng', $selectedMonth->year)
            ->whereMonth('Date_Part_Ng', $selectedMonth->month)
            ->whereNotNull('penanggungjawab')
            ->where('penanggungjawab', '!=', '')
            ->select('penanggungjawab', 'Divisi', 'Total_Part_Ng', 'Code_Item_Rack', 'Code_Rack', 'harga_snapshot');

        $this->applyNonDateFilters($query, $request);

        $parts    = $query->get();
        $priceMap = $this->getPriceMap();

        $memberAgg = [];
        $areaAgg   = [];

        foreach ($parts as $part) {
            if ($part->harga_snapshot !== null) {
                $harga = (float) $part->harga_snapshot;
            } else {
                $harga = $priceMap[$part->Code_Item_Rack] ?? $priceMap[$part->Code_Rack] ?? 0;
            }
            $cost = $harga * $part->Total_Part_Ng;

            // Aggregasi per penanggungjawab
            $pj = $part->penanggungjawab;
            if (!isset($memberAgg[$pj])) {
                $memberAgg[$pj] = ['total_qty' => 0, 'frekuensi' => 0, 'total_cost' => 0];
            }
            $memberAgg[$pj]['total_qty']  += $part->Total_Part_Ng;
            $memberAgg[$pj]['frekuensi']  += 1;
            $memberAgg[$pj]['total_cost'] += $cost;

            // Aggregasi per Divisi
            $div = $part->Divisi ?? 'Tanpa Divisi';
            if (!isset($areaAgg[$div])) {
                $areaAgg[$div] = ['total_qty' => 0, 'frekuensi' => 0, 'total_cost' => 0];
            }
            $areaAgg[$div]['total_qty']  += $part->Total_Part_Ng;
            $areaAgg[$div]['frekuensi']  += 1;
            $areaAgg[$div]['total_cost'] += $cost;
        }

        // Urutkan descending by total_cost
        uasort($memberAgg, fn($a, $b) => $b['total_cost'] <=> $a['total_cost']);
        uasort($areaAgg,   fn($a, $b) => $b['total_cost'] <=> $a['total_cost']);

        $memberRankings = array_map(fn($name, $data) => ['name' => $name] + $data, array_keys($memberAgg), $memberAgg);
        $areaRankings   = array_map(fn($name, $data) => ['name' => $name] + $data, array_keys($areaAgg), $areaAgg);

        $prevMonth  = $selectedMonth->copy()->subMonth()->format('Y-m');
        $nextMonth  = $selectedMonth->copy()->addMonth()->format('Y-m');
        $monthLabel = $selectedMonth->translatedFormat('F Y');

        return view('admin.ranking', compact('memberRankings', 'areaRankings', 'prevMonth', 'nextMonth', 'monthLabel'));
    }

    public function exportCsv(Request $request)
    {
        $query = PartNg::with('member');
        $query = $this->applyFilters($query, $request);

        if ($request->filled('status')) {
            if ($request->status === 'processed') {
                $query->whereNotNull('penanggungjawab')->where('penanggungjawab', '!=', '');
            } elseif ($request->status === 'unprocessed') {
                $query->where(function ($q) {
                    $q->whereNull('penanggungjawab')->orWhere('penanggungjawab', '');
                });
            }
        }

        if (!$request->filled('date') && !$request->filled('month') && !$request->filled('week')) {
            $query->whereDate('Date_Part_Ng', Carbon::today());
        }

        $parts = $query->orderBy('Date_Part_Ng', 'asc')->get();

        try {
            $exportService = new ExcelExportService();
            $xlsxFile      = $exportService->generate($parts, $request);

            if (!$xlsxFile || !file_exists($xlsxFile)) {
                throw new \RuntimeException('File export gagal dibuat.');
            }

            $divisi   = preg_replace('/[^A-Za-z0-9_\-]/', '_', $request->divisi   ?? 'Semua');
            $category = preg_replace('/[^A-Za-z0-9_\-]/', '_', $request->category ?? 'Semua');
            $filename = "Part_NG_{$divisi}_{$category}_" . Carbon::now()->format('Ymd_His') . ".xlsx";

            return response()->download($xlsxFile, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Helper validasi input
    // -------------------------------------------------------------------------

    private function isValidDate(string $value): bool
    {
        try {
            Carbon::createFromFormat('Y-m-d', $value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function isValidYearMonth(string $value): bool
    {
        try {
            Carbon::createFromFormat('Y-m', $value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
