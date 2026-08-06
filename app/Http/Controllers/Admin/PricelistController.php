<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pricelist;
use App\Models\Rack;
use App\Models\Currency;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Cache;

class PricelistController extends Controller
{
    public function index(Request $request)
    {
        $query = Pricelist::orderBy('no');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_part', 'like', "%{$search}%")
                  ->orWhere('nama_part', 'like', "%{$search}%")
                  ->orWhere('no_rak', 'like', "%{$search}%");
            });
        }

        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        $pricelists = $query->paginate(100)->withQueryString();

        $currencies = Currency::orderBy('code')->pluck('code');

        return view('admin.pricelist.index', compact('pricelists', 'currencies'));
    }

    public function importForm()
    {
        return view('admin.pricelist.import');
    }

    public function importExcel(Request $request)
    {
        set_time_limit(600);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $invalid = 0;

        // Batch 1: kumpulin semua kode_part dulu
        $kodeParts = [];
        $rowData = [];
        foreach ($rows as $index => $row) {
            if ($index == 0) continue;
            $kodePart = trim($row[0] ?? '');
            $currency = strtoupper(trim($row[1] ?? ''));
            $hargaRaw = trim($row[2] ?? '');
            if (empty($kodePart) || $hargaRaw === '') {
                $invalid++;
                continue;
            }
            $kodeParts[] = $kodePart;
            $rowData[] = compact('kodePart', 'currency', 'hargaRaw');
        }

        // Batch 2: query Rack sekali
        $racks = Rack::whereIn('Code_Item_Rack', $kodeParts)->get()->keyBy('Code_Item_Rack');

        // Batch 3: ambil semua Currency sekali
        $currencyModels = Currency::all()->keyBy('code');

        // Batch 4: ambil semua existing Pricelist
        $existingPrices = Pricelist::whereIn('kode_part', $kodeParts)->get()->keyBy('kode_part');
        $maxNo = Pricelist::max('no') ?? 0;

        foreach ($rowData as $data) {
            $kodePart = $data['kodePart'];
            $currency = $data['currency'];
            $hargaRaw = $data['hargaRaw'];

            $rack = $racks->get($kodePart);
            if (!$rack) {
                $invalid++;
                continue;
            }
            $noRak    = $rack->Code_Rack;
            $namaPart = $rack->Name_Item_Rack;

            $hargaAsli = $this->parseNumber($hargaRaw);

            $currency = match ($currency) {
                'RUPIAH' => 'IDR',
                'YEN'    => 'JPY',
                default  => $currency,
            };

            $currencyModel = $currencyModels->get($currency);
            if (!$currencyModel) {
                $invalid++;
                continue;
            }

            $hargaUsd = $currencyModel->is_base ? $hargaAsli : $currencyModel->convertToBase($hargaAsli);

            if ($hargaUsd <= 0 || $hargaUsd > 1000000) {
                $invalid++;
                continue;
            }

            $existing = $existingPrices->get($kodePart);

            if ($existing) {
                if ((float) $existing->harga_asli == $hargaAsli &&
                    $existing->currency === $currency) {
                    $skipped++;
                    continue;
                }

                $existing->update([
                    'no_rak'     => $noRak,
                    'nama_part'  => $namaPart,
                    'harga_asli' => $hargaAsli,
                    'currency'   => $currency,
                ]);
                $updated++;
            } else {
                $maxNo++;
                Pricelist::create([
                    'no'         => $maxNo,
                    'no_rak'     => $noRak,
                    'kode_part'  => $kodePart,
                    'nama_part'  => $namaPart,
                    'harga_asli' => $hargaAsli,
                    'currency'   => $currency,
                ]);
                $imported++;
            }
        }

        $msg = "Import selesai. {$imported} data baru ditambahkan";
        if ($updated > 0) $msg .= ", {$updated} data diperbarui";
        if ($skipped > 0) $msg .= ", {$skipped} data sama (dilewati)";
        if ($invalid > 0) $msg .= ", {$invalid} data tidak valid (dilewati)";
        $msg .= ".";

        // Buang cache harga agar laporan langsung pakai data terbaru
        Cache::forget('pricelist_map');

        return redirect()->route('admin.pricelist.index')
            ->with('success', $msg);
    }

    private function parseNumber(string $value): float
    {
        $value = trim($value);
        if ($value === '') return 0;

        // Cek jika mengandung koma dan titik sekaligus
        if (str_contains($value, ',') && str_contains($value, '.')) {
            if (strpos($value, ',') < strpos($value, '.')) {
                // Format US: 1,234.56 -> hapus koma ribuan
                $value = str_replace(',', '', $value);
            } else {
                // Format Indo: 1.234,56 -> hapus titik ribuan, ganti koma jadi titik desimal
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            }
        } elseif (str_contains($value, ',')) {
            // Hanya ada koma (misal: 1234,56) -> ganti koma jadi titik desimal
            $value = str_replace(',', '.', $value);
        } else {
            // Hanya ada titik atau tanpa titik (misal: 1.234.567 atau 7.19 atau 869981)
            if (substr_count($value, '.') > 1) {
                // Banyak titik -> pemisah ribuan tanpa desimal
                $value = str_replace('.', '', $value);
            }
        }

        return (float) $value;
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_part' => 'required|string',
            'harga_asli'=> 'required|numeric',
            'currency'  => 'required|string',
        ]);

        $pricelist = Pricelist::findOrFail($id);

        $pricelist->update([
            'kode_part' => $request->kode_part,
            'no_rak'    => $request->no_rak ?? $pricelist->no_rak,
            'nama_part' => $request->nama_part ?? $pricelist->nama_part,
            'harga_asli'=> (float) $request->harga_asli,
            'currency'  => strtoupper($request->currency),
        ]);

        // Buang cache harga agar laporan langsung pakai data terbaru
        Cache::forget('pricelist_map');

        return redirect()->route('admin.pricelist.index')->with('success', 'Data pricelist berhasil diperbarui.');
    }
}
