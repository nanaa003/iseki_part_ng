<?php

namespace App\Http\Controllers\Leader;

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
    private function applyUserAreaFilter($query): void
    {
        $user = auth()->user();
        if ($user && $user->hasAreaRestriction()) {
            $user->applyAreaFilter($query);
        }
    }

    private function getPriceMap(): array
    {
        return Cache::remember('pricelist_map', 600, function () {
            $map = [];
            foreach (Pricelist::all() as $p) {
                if ($p->kode_part) $map[$p->kode_part] = $p->harga_usd;
            }
            return $map;
        });
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('date') && $this->isValidDate($request->date)) {
            $query->whereDate('Date_Part_Ng', $request->date);
        }

        if ($request->filled('month') && $this->isValidYearMonth($request->month)) {
            $date = Carbon::createFromFormat('Y-m', $request->month);
            $query->whereYear('Date_Part_Ng', $date->year)
                  ->whereMonth('Date_Part_Ng', $date->month);
        }

        if ($request->filled('category')) {
            if ($request->category === 'bukan tanggung jawab') {
                $query->where('Category_Part_Ng', 'like', 'bukan tanggung jawab%');
            } else {
                $query->where('Category_Part_Ng', $request->category);
            }
        }

        if ($request->filled('divisi')) {
            $div = $request->divisi;
            if ($div === 'Assembling') {
                $query->where(function($q) {
                    $q->whereIn('Divisi', ['Assembling', 'Mower', 'MOWER', 'MAIN LINE', 'SUB ASSY', 'SUB ENGINE', 'TRANSMISI', 'INSPEKSI', 'REPAIR']);
                });
            } elseif ($div === 'Painting') {
                $query->where(function($q) {
                    $q->whereIn('Divisi', ['Painting', 'PAINTING A', 'PAINTING B']);
                });
            } elseif ($div === 'DST') {
                $query->where('Divisi', 'DST');
            } else {
                $query->where(function($q) use ($div) {
                    $q->where('proses', $div)
                      ->orWhere('Divisi', strtoupper($div))
                      ->orWhere('Divisi', str_replace(' ', '', strtoupper($div)))
                      ->orWhere('Divisi', str_replace('SUBASSY', 'SUB ASSY', strtoupper($div)))
                      ->orWhere('Divisi', str_replace('MAINLINE', 'MAIN LINE', strtoupper($div)));
                });
            }
        }

        if ($request->filled('week') && !$request->filled('date')) {
            $this->applyWeekFilter($query, $request);
        }

        return $query;
    }

    private function applyWeekFilter($query, Request $request): void
    {
        if ($request->filled('month') && $this->isValidYearMonth($request->month)) {
            $h = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
        } else {
            $h = Carbon::today()->startOfMonth();
        }

        $weekNum = max(1, (int) $request->week);
        $startDay = ($weekNum - 1) * 7 + 1;
        $endDay   = min($weekNum * 7, $h->daysInMonth);

        if ($startDay > $h->daysInMonth) {
            return;
        }

        $query->whereYear('Date_Part_Ng', $h->year)
              ->whereMonth('Date_Part_Ng', $h->month)
              ->whereDay('Date_Part_Ng', '>=', $startDay)
              ->whereDay('Date_Part_Ng', '<=', $endDay);
    }

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
            $div = $request->divisi;
            if ($div === 'Assembling') {
                $query->where(function($q) {
                    $q->whereIn('Divisi', ['Assembling', 'Mower', 'MOWER', 'MAIN LINE', 'SUB ASSY', 'SUB ENGINE', 'TRANSMISI', 'INSPEKSI', 'REPAIR']);
                });
            } elseif ($div === 'Painting') {
                $query->where(function($q) {
                    $q->whereIn('Divisi', ['Painting', 'PAINTING A', 'PAINTING B']);
                });
            } elseif ($div === 'DST') {
                $query->where('Divisi', 'DST');
            } else {
                $query->where(function($q) use ($div) {
                    $q->where('proses', $div)
                      ->orWhere('Divisi', strtoupper($div))
                      ->orWhere('Divisi', str_replace(' ', '', strtoupper($div)))
                      ->orWhere('Divisi', str_replace('SUBASSY', 'SUB ASSY', strtoupper($div)))
                      ->orWhere('Divisi', str_replace('MAINLINE', 'MAIN LINE', strtoupper($div)));
                });
            }
        }

        if ($request->filled('week')) {
            $this->applyWeekFilter($query, $request);
        }
    }

    private function getTotalChartData(Carbon $month, Request $request = null)
    {
        $query = PartNg::query()
            ->whereYear('Date_Part_Ng', $month->year)
            ->whereMonth('Date_Part_Ng', $month->month);

        $this->applyUserAreaFilter($query);

        if ($request) {
            $this->applyNonDateFilters($query, $request);
        }

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

    private function getProcessedComparison(Carbon $month, Request $request = null)
    {
        $base = PartNg::query()
            ->whereYear('Date_Part_Ng', $month->year)
            ->whereMonth('Date_Part_Ng', $month->month);

        $this->applyUserAreaFilter($base);

        if ($request) {
            $this->applyNonDateFilters($base, $request);
        }

        $processed = (clone $base)
            ->whereNotNull('penanggungjawab')->where('penanggungjawab', '!=', '')
            ->count();

        $unprocessed = (clone $base)
            ->where(function ($q) {
                $q->whereNull('penanggungjawab')->orWhere('penanggungjawab', '');
            })
            ->count();

        return [$processed, $unprocessed];
    }

    private function getCostChartData(Carbon $month, Request $request = null)
    {
        $query = PartNg::query()
            ->whereYear('Date_Part_Ng', $month->year)
            ->whereMonth('Date_Part_Ng', $month->month)
            ->select('Date_Part_Ng', 'Code_Item_Rack', 'Code_Rack', 'Total_Part_Ng', 'harga_snapshot');

        $this->applyUserAreaFilter($query);

        if ($request) {
            $this->applyNonDateFilters($query, $request);
        }

        $parts    = $query->get();
        $priceMap = $this->getPriceMap();

        $daily = [];
        foreach ($parts as $part) {
            $date  = Carbon::parse($part->Date_Part_Ng)->format('Y-m-d');
            // Prioritas: harga_snapshot (dilock saat input) -> fallback pricelist terkini
            $harga = (!is_null($part->harga_snapshot) && (float)$part->harga_snapshot > 0) ? (float)$part->harga_snapshot : ($priceMap[$part->Code_Item_Rack] ?? 0);
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

        return view('leader.dashboard', compact(
            'chartDates', 'chartTotals',
            'processedTotal', 'unprocessedTotal',
            'costDates', 'costTotals', 'totalCost',
            'selectedMonth', 'prevMonth', 'nextMonth', 'monthLabel'
        ));
    }

    private function attachCost($parts): float
    {
        $priceMap  = $this->getPriceMap();
        $totalCost = 0.0;

        foreach ($parts as $part) {
            // Prioritas: harga_snapshot (dilock saat input) -> fallback pricelist terkini.
            // Jika keduanya tidak ada -> harga_found = false (tampil "Harga tidak ditemukan" di laporan).
            $snapshotAda = !is_null($part->harga_snapshot) && (float)$part->harga_snapshot > 0;
            $hargaList   = $priceMap[$part->Code_Item_Rack] ?? 0;
            if ($snapshotAda) {
                $harga = (float)$part->harga_snapshot;
                $part->harga_found = true;
            } elseif ((float)$hargaList > 0) {
                $harga = (float)$hargaList;
                $part->harga_found = true;
            } else {
                $harga = 0;
                $part->harga_found = false;
            }
            $part->harga_satuan = $harga;
            $part->cost         = $harga * $part->Total_Part_Ng;
            $totalCost         += $part->cost;
        }

        return $totalCost;
    }

    private function buildReport(Request $request, string $view, ?string $status, bool $monthly): \Illuminate\View\View
    {
        $query = PartNg::with('member');
        $this->applyUserAreaFilter($query);
        $query = $this->applyFilters($query, $request);

        $selectedMonth = Carbon::now();
        if (!$request->filled('date') && !$request->filled('month')) {
            if ($monthly) {
                $query->whereYear('Date_Part_Ng', $selectedMonth->year)
                      ->whereMonth('Date_Part_Ng', $selectedMonth->month);
            } else {
                $query->whereDate('Date_Part_Ng', Carbon::today());
            }
        }

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
        return $this->buildReport($request, 'leader.report', null, false);
    }

    public function reportMonthly(Request $request)
    {
        $isDateFilter = $request->filled('date') && $this->isValidDate($request->date);
        $isMonthFilter = $request->filled('month') && $this->isValidYearMonth($request->month);

        $query = PartNg::with('member');
        $this->applyUserAreaFilter($query);
        $query = $this->applyFilters($query, $request);

        if (!$isDateFilter && !$isMonthFilter) {
            $query->whereYear('Date_Part_Ng', Carbon::now()->year)
                  ->whereMonth('Date_Part_Ng', Carbon::now()->month);
        }

        $parts     = $query->orderBy('Date_Part_Ng', 'desc')->get();
        $totalCost = $this->attachCost($parts);

        if ($isDateFilter) {
            $selectedDate = Carbon::createFromFormat('Y-m-d', $request->date);
            $prevDate = $selectedDate->copy()->subDay()->format('Y-m-d');
            $nextDate = $selectedDate->copy()->addDay()->format('Y-m-d');
            $dateLabel = $selectedDate->translatedFormat('d F Y');
            $monthLabel = $dateLabel;
            $prevMonth = $prevDate;
            $nextMonth = $nextDate;
        } else {
            $selectedMonth = $isMonthFilter
                ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()
                : Carbon::now()->startOfMonth();
            $prevMonth = $selectedMonth->copy()->subMonth()->format('Y-m');
            $nextMonth = $selectedMonth->copy()->addMonth()->format('Y-m');
            $monthLabel = $selectedMonth->translatedFormat('F Y');
        }

        return view('leader.report-monthly', compact('parts', 'totalCost', 'prevMonth', 'nextMonth', 'monthLabel'));
    }

    public function reportUnprocessed(Request $request)
    {
        return $this->buildReport($request, 'leader.report-unprocessed', 'unprocessed', true);
    }

    public function reportProcessed(Request $request)
    {
        return $this->buildReport($request, 'leader.report-processed', 'processed', true);
    }

    public function process(Request $request, $id)
    {
        $request->validate([
            'penyebab'       => 'required|string|max:255',
            'penanganan'     => 'required|string|max:255',
            'penanggungjawab' => 'nullable|string|max:255',
        ]);

        $query = PartNg::where('Id_Part_Ng', $id);
        $this->applyUserAreaFilter($query);
        $part = $query->firstOrFail();

        $part->update([
            'penanggungjawab' => $request->filled('penanggungjawab') ? $request->penanggungjawab : $part->penanggungjawab,
            'penyebab'   => $request->penyebab,
            'penanganan' => $request->penanganan,
            'proses_at'  => Carbon::now(),
            'Date_Part_Ng' => DB::raw('Date_Part_Ng'),
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

        $this->applyUserAreaFilter($query);
        $this->applyNonDateFilters($query, $request);

        $parts    = $query->get();
        $priceMap = $this->getPriceMap();

        $memberAgg = [];
        $areaAgg   = [];

        foreach ($parts as $part) {
            // Prioritas: harga_snapshot (dilock saat input) -> fallback pricelist terkini
            $harga = (!is_null($part->harga_snapshot) && (float)$part->harga_snapshot > 0) ? (float)$part->harga_snapshot : ($priceMap[$part->Code_Item_Rack] ?? 0);
            $cost = $harga * $part->Total_Part_Ng;

            $pjRaw = trim($part->penanggungjawab);
            $pj = (stripos($pjRaw, 'lain') !== false) ? 'Lain Lain' : $pjRaw;
            if (!isset($memberAgg[$pj])) {
                $memberAgg[$pj] = ['total_qty' => 0, 'frekuensi' => 0, 'total_cost' => 0];
            }
            $memberAgg[$pj]['total_qty']  += $part->Total_Part_Ng;
            $memberAgg[$pj]['frekuensi']  += 1;
            $memberAgg[$pj]['total_cost'] += $cost;

            $div = $part->Divisi ?? 'Tanpa Divisi';
            if (!isset($areaAgg[$div])) {
                $areaAgg[$div] = ['total_qty' => 0, 'frekuensi' => 0, 'total_cost' => 0];
            }
            $areaAgg[$div]['total_qty']  += $part->Total_Part_Ng;
            $areaAgg[$div]['frekuensi']  += 1;
            $areaAgg[$div]['total_cost'] += $cost;
        }

        uasort($memberAgg, fn($a, $b) => $b['total_cost'] <=> $a['total_cost']);
        uasort($areaAgg,   fn($a, $b) => $b['total_cost'] <=> $a['total_cost']);

        $memberRankings = array_map(fn($name, $data) => ['name' => $name] + $data, array_keys($memberAgg), $memberAgg);
        $areaRankings   = array_map(fn($name, $data) => ['name' => $name] + $data, array_keys($areaAgg), $areaAgg);

        $prevMonth  = $selectedMonth->copy()->subMonth()->format('Y-m');
        $nextMonth  = $selectedMonth->copy()->addMonth()->format('Y-m');
        $monthLabel = $selectedMonth->translatedFormat('F Y');

        return view('leader.ranking', compact('memberRankings', 'areaRankings', 'prevMonth', 'nextMonth', 'monthLabel'));
    }

    public function editPartNg($id)
    {
        $query = PartNg::where('Id_Part_Ng', $id);
        $this->applyUserAreaFilter($query);
        $part = $query->firstOrFail();

        return view('leader.edit', compact('part'));
    }

    public function updatePartNg(Request $request, $id)
    {
        $query = PartNg::where('Id_Part_Ng', $id);
        $this->applyUserAreaFilter($query);
        $part = $query->firstOrFail();

        $request->validate([
            'Desc_Part_Ng' => 'required|string',
            'Category_Part_Ng' => 'required|string',
            'Total_Part_Ng' => 'required|integer|min:1',
            'Code_Rack' => 'nullable|string|max:255',
            'Code_Item_Rack' => 'nullable|string|max:255',
            'Name_Item_Rack' => 'nullable|string|max:255',
            'penanggungjawab' => 'nullable|string|max:255',
            'penyebab' => 'nullable|string|max:255',
            'penanganan' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:5120',
            'photo_2' => 'nullable|image|max:5120',
            'photo_3' => 'nullable|image|max:5120',
        ]);

        $data = $request->only([
            'Desc_Part_Ng', 'Category_Part_Ng', 'Total_Part_Ng',
            'Code_Rack', 'Code_Item_Rack', 'Name_Item_Rack'
        ]);

        if ($part->penanggungjawab) {
            $data['penanggungjawab'] = $request->input('penanggungjawab');
            $data['penyebab'] = $request->input('penyebab');
            $data['penanganan'] = $request->input('penanganan');
            $data['proses_at'] = Carbon::now();
        }

        $data['Date_Part_Ng'] = DB::raw('Date_Part_Ng');

        for ($i = 1; $i <= 3; $i++) {
            $field = $i === 1 ? 'photo' : 'photo_' . $i;
            $dbField = $i === 1 ? 'Photo_Path_Part_Ng' : ($i === 2 ? 'Photo_Path_Part_Ng_2' : 'Photo_Path_Part_Ng_3');

            if ($request->hasFile($field)) {
                if ($part->$dbField && file_exists(public_path($part->$dbField))) {
                    unlink(public_path($part->$dbField));
                }
                $file = $request->file($field);
                $filename = 'part_ng_' . $id . '_' . $i . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/part_ng'), $filename);
                $data[$dbField] = 'uploads/part_ng/' . $filename;
            }

            if ($request->has('remove_photo_' . $i) && $request->input('remove_photo_' . $i) == '1') {
                if ($part->$dbField && file_exists(public_path($part->$dbField))) {
                    unlink(public_path($part->$dbField));
                }
                $data[$dbField] = null;
            }
        }

        $part->update($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data Part NG berhasil diperbarui.']);
        }

        return redirect()->back()->with('success', 'Data Part NG berhasil diperbarui.');
    }

    public function destroyPartNg($id)
    {
        $query = PartNg::where('Id_Part_Ng', $id);
        $this->applyUserAreaFilter($query);
        $part = $query->firstOrFail();

        for ($i = 1; $i <= 3; $i++) {
            $dbField = $i === 1 ? 'Photo_Path_Part_Ng' : ($i === 2 ? 'Photo_Path_Part_Ng_2' : 'Photo_Path_Part_Ng_3');
            if ($part->$dbField && file_exists(public_path($part->$dbField))) {
                unlink(public_path($part->$dbField));
            }
        }

        $part->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data Part NG berhasil dihapus.']);
        }

        return redirect()->back()->with('success', 'Data Part NG berhasil dihapus.');
    }

    public function exportCsv(Request $request)
    {
        $query = PartNg::with('member');
        $this->applyUserAreaFilter($query);
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
            $filename = "Part_NG_Leader_{$divisi}_{$category}_" . Carbon::now()->format('Ymd_His') . ".xlsx";

            return response()->download($xlsxFile, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }

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
