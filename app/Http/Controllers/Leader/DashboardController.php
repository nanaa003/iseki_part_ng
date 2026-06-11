<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PartNg;
use App\Models\Pricelist;
use App\Services\ExcelExportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function applyFilters($query, Request $request)
    {
        if ($request->has('date') && $request->date) {
            $query->whereDate('Date_Part_Ng', $request->date);
        }

        if ($request->has('month') && $request->month) {
            $date = Carbon::createFromFormat('Y-m', $request->month);
            $query->whereYear('Date_Part_Ng', $date->year)
                  ->whereMonth('Date_Part_Ng', $date->month);
        }

        if ($request->has('category') && $request->category) {
            if ($request->category == 'bukan tanggung jawab') {
                $query->where('Category_Part_Ng', 'like', 'bukan tanggung jawab%');
            } else {
                $query->where('Category_Part_Ng', $request->category);
            }
        }

        if ($request->has('divisi') && $request->divisi) {
            $query->where('Divisi', $request->divisi);
        }

        if ($request->has('week') && $request->week) {
            if ($request->has('month') && $request->month) {
                $h = Carbon::createFromFormat('Y-m', $request->month);
            } elseif ($request->has('date') && $request->date) {
                $h = Carbon::parse($request->date);
            } else {
                $h = Carbon::today();
            }
            $weekNum = (int)$request->week;
            $ws = ($weekNum - 1) * 7 + 1;
            $we = min($weekNum * 7, $h->daysInMonth);
            $query->whereYear('Date_Part_Ng', $h->year)
                  ->whereMonth('Date_Part_Ng', $h->month)
                  ->whereDay('Date_Part_Ng', '>=', $ws)
                  ->whereDay('Date_Part_Ng', '<=', $we);
        }

        return $query;
    }

    private function getTotalChartData(Carbon $month, Request $request = null)
    {
        $query = PartNg::query()
            ->whereYear('Date_Part_Ng', $month->year)
            ->whereMonth('Date_Part_Ng', $month->month);

        if ($request) {
            $query = $this->applyFilters($query, $request);
        }

        $raw = $query->select(
                DB::raw('DATE(Date_Part_Ng) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw('DATE(Date_Part_Ng)'))
            ->orderBy('date', 'asc')
            ->get();

        $dates = $raw->pluck('date')->map(function ($date) {
            return Carbon::parse($date)->format('d M');
        });
        $totals = $raw->pluck('total');

        return [$dates, $totals];
    }

    private function getProcessedComparison(Carbon $month, Request $request = null)
    {
        $base = PartNg::query()
            ->whereYear('Date_Part_Ng', $month->year)
            ->whereMonth('Date_Part_Ng', $month->month);

        if ($request) {
            $base = $this->applyFilters($base, $request);
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
        $query = PartNg::whereYear('Date_Part_Ng', $month->year)
            ->whereMonth('Date_Part_Ng', $month->month);

        if ($request) {
            $query = $this->applyFilters($query, $request);
        }

        $parts = $query->get();

        $priceMap = [];
        foreach (Pricelist::all() as $p) {
            if ($p->kode_part) $priceMap[$p->kode_part] = $p->harga_usd;
            if ($p->no_rak) $priceMap[$p->no_rak] = $p->harga_usd;
        }

        $daily = [];
        foreach ($parts as $part) {
            $date = Carbon::parse($part->Date_Part_Ng)->format('Y-m-d');
            $harga = $priceMap[$part->Code_Item_Rack] ?? $priceMap[$part->Code_Rack] ?? 0;
            $daily[$date] = ($daily[$date] ?? 0) + ($harga * $part->Total_Part_Ng);
        }

        $dates = [];
        $totals = [];
        foreach ($month->copy()->startOfMonth()->daysUntil($month->copy()->endOfMonth()) as $day) {
            $d = $day->format('Y-m-d');
            $dates[] = $day->format('d M');
            $totals[] = $daily[$d] ?? 0;
        }

        return [$dates, $totals, array_sum($totals)];
    }

    public function dashboard(Request $request)
    {
        $selectedMonth = $request->month ? Carbon::parse($request->month)->startOfMonth() : Carbon::now()->startOfMonth();

        [$chartDates, $chartTotals] = $this->getTotalChartData($selectedMonth, $request);
        [$processedTotal, $unprocessedTotal] = $this->getProcessedComparison($selectedMonth, $request);
        [$costDates, $costTotals, $totalCost] = $this->getCostChartData($selectedMonth, $request);

        $prevMonth = $selectedMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $selectedMonth->copy()->addMonth()->format('Y-m');
        $monthLabel = $selectedMonth->translatedFormat('F Y');

        return view('leader.dashboard', compact(
            'chartDates', 'chartTotals',
            'processedTotal', 'unprocessedTotal',
            'costDates', 'costTotals', 'totalCost',
            'selectedMonth', 'prevMonth', 'nextMonth', 'monthLabel'
        ));
    }

    private function attachCost($parts)
    {
        $priceMap = [];
        foreach (Pricelist::all() as $p) {
            if ($p->kode_part) $priceMap[$p->kode_part] = $p->harga_usd;
            if ($p->no_rak) $priceMap[$p->no_rak] = $p->harga_usd;
        }

        $totalCost = 0;
        foreach ($parts as $part) {
            $harga = $priceMap[$part->Code_Item_Rack] ?? $priceMap[$part->Code_Rack] ?? 0;
            $part->harga_satuan = $harga;
            $part->cost = $harga * $part->Total_Part_Ng;
            $totalCost += $part->cost;
        }

        return $totalCost;
    }

    public function reportMonthly(Request $request)
    {
        $isDateFilter = $request->filled('date') && $this->isValidDate($request->date);
        $isMonthFilter = $request->filled('month') && $this->isValidYearMonth($request->month);

        $query = PartNg::with('member');
        $query = $this->applyFilters($query, $request);

        if (!$isDateFilter && !$isMonthFilter) {
            $query->whereDate('Date_Part_Ng', Carbon::today());
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

    public function reportUnprocessed(Request $request)
    {
        $selectedMonth = $request->filled('month')
            ? Carbon::parse($request->month)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $query = PartNg::with('member');
        $query = $this->applyFilters($query, $request);
        $query->whereYear('Date_Part_Ng', $selectedMonth->year)
              ->whereMonth('Date_Part_Ng', $selectedMonth->month);

        $query->where(function ($q) {
            $q->whereNull('penanggungjawab')->orWhere('penanggungjawab', '');
        });

        $parts     = $query->orderBy('Date_Part_Ng', 'desc')->get();
        $totalCost = $this->attachCost($parts);

        $prevMonth  = $selectedMonth->copy()->subMonth()->format('Y-m');
        $nextMonth  = $selectedMonth->copy()->addMonth()->format('Y-m');
        $monthLabel = $selectedMonth->translatedFormat('F Y');

        return view('leader.report-unprocessed', compact('parts', 'totalCost', 'selectedMonth', 'prevMonth', 'nextMonth', 'monthLabel'));
    }

    public function reportProcessed(Request $request)
    {
        $selectedMonth = $request->filled('month')
            ? Carbon::parse($request->month)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $query = PartNg::with('member');
        $query = $this->applyFilters($query, $request);
        $query->whereYear('Date_Part_Ng', $selectedMonth->year)
              ->whereMonth('Date_Part_Ng', $selectedMonth->month);

        $query->whereNotNull('penanggungjawab')->where('penanggungjawab', '!=', '');

        $parts     = $query->orderBy('Date_Part_Ng', 'desc')->get();
        $totalCost = $this->attachCost($parts);

        $prevMonth  = $selectedMonth->copy()->subMonth()->format('Y-m');
        $nextMonth  = $selectedMonth->copy()->addMonth()->format('Y-m');
        $monthLabel = $selectedMonth->translatedFormat('F Y');

        return view('leader.report-processed', compact('parts', 'totalCost', 'selectedMonth', 'prevMonth', 'nextMonth', 'monthLabel'));
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
            'penyebab' => $request->penyebab,
            'penanganan' => $request->penanganan,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data Part NG berhasil diproses.']);
        }

        return back()->with('success', 'Data Part NG berhasil diproses.');
    }

    public function exportCsv(Request $request)
    {
        $query = PartNg::with('member');
        $query = $this->applyFilters($query, $request);

        if ($request->has('status') && $request->status == 'processed') {
            $query->whereNotNull('penanggungjawab')->where('penanggungjawab', '!=', '');
        } elseif ($request->has('status') && $request->status == 'unprocessed') {
            $query->where(function ($q) {
                $q->whereNull('penanggungjawab')->orWhere('penanggungjawab', '');
            });
        }

        if (!$request->has('date') && !$request->has('month') && !$request->has('week')) {
            $query->whereDate('Date_Part_Ng', Carbon::today());
        }

        $parts = $query->orderBy('Date_Part_Ng', 'asc')->get();

        $exportService = new ExcelExportService();
        $xlsxFile = $exportService->generate($parts, $request);

        $divisi = $request->divisi ?? 'Semua';
        $category = $request->category ?? 'Semua';
        $filename = "Part_NG_Leader_{$divisi}_{$category}_" . Carbon::now()->format('Ymd_His') . ".xlsx";
        $filename = str_replace(' ', '_', $filename);

        return response()->download($xlsxFile, $filename)->deleteFileAfterSend(true);
    }
}
