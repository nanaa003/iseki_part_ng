<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PartNg;
use Carbon\Carbon;

class AdminController extends Controller
{
    private function getFilteredData(Request $request)
    {
        $query = PartNg::with('member');

        if ($request->has('date') && $request->date) {
            $query->whereDate('Date_Part_Ng', $request->date);
        }

        if ($request->has('month') && $request->month) {
            // $request->month is in format YYYY-MM
            $date = Carbon::createFromFormat('Y-m', $request->month);
            $query->whereYear('Date_Part_Ng', $date->year)
                  ->whereMonth('Date_Part_Ng', $date->month);
        }

        if ($request->has('category') && $request->category) {
            $query->where('Category_Part_Ng', $request->category);
        }

        return $query->orderBy('Date_Part_Ng', 'desc')->get();
    }

    public function dashboard(Request $request)
    {
        $parts = $this->getFilteredData($request);
        return view('admin.dashboard', compact('parts'));
    }

    public function exportCsv(Request $request)
    {
        $parts = $this->getFilteredData($request);

        $filename = "Laporan_Part_NG_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($parts, $request) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel UTF-8
            fputs($file, "\xEF\xBB\xBF");

            $periode = 'Semua Waktu';
            if ($request->has('date') && $request->date) {
                $periode = Carbon::parse($request->date)->translatedFormat('d F Y');
            } elseif ($request->has('month') && $request->month) {
                $periode = Carbon::createFromFormat('Y-m', $request->month)->translatedFormat('F Y');
            }

            // Title and Date section
            fputcsv($file, ['LAPORAN PART NG'], ';');
            fputcsv($file, ['Tanggal / Bulan', $periode], ';');
            fputcsv($file, [], ';');

            // Header matching the template
            fputcsv($file, [
                "発生月日\nTgl. / Bln. Terjadi",
                "管理No\nNo. Pengendalian",
                "棚番号\nNo. Rak",
                "コードNo\nKode Part",
                "部品名\nNama Part",
                "不適合内容\nKonten Ketidaksesuaian",
                "工程\nProses",
                "個数\nJml. Pcs",
                "原因\nPenyebab",
                "再発防止\nPencegahan Pengulangan",
                "有効性確認日\nTgl. Cek Keefektifan",
                "備考\nCatatan"
            ], ';');

            $no = 1;
            foreach ($parts as $p) {
                fputcsv($file, [
                    Carbon::parse($p->Date_Part_Ng)->format('d-M'),
                    $no++,
                    $p->Code_Rack,
                    $p->Code_Item_Rack,
                    $p->Name_Item_Rack,
                    strtoupper($p->Desc_Part_Ng),
                    'SUB',
                    '1',
                    strtoupper($p->Category_Part_Ng),
                    '',
                    Carbon::parse($p->Date_Part_Ng)->format('d-M'),
                    $p->member ? strtoupper($p->member->nama) : '-'
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
