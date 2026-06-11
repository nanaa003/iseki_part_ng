<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pricelist;
use App\Models\Rack;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PricelistController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Pricelist::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('no_rak', 'like', '%' . $search . '%')
                  ->orWhere('kode_part', 'like', '%' . $search . '%')
                  ->orWhere('nama_part', 'like', '%' . $search . '%');
            });
        }

        $pricelists = $query->orderBy('no')->paginate(100)->withQueryString();

        return view('admin.pricelist.index', compact('pricelists', 'search'));
    }

    public function importForm()
    {
        return view('admin.pricelist.import');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Use formatData = false to get raw numbers (floats/ints) directly from Excel
        $rows = $worksheet->toArray(null, true, false, false);

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $invalid = 0;
        $notFound = 0;

        foreach ($rows as $index => $row) {
            if ($index == 0) continue;

            // New Column order: kode part, currency, harga
            $kodePart = trim((string)($row[0] ?? ''));
            $currency = strtoupper(trim((string)($row[1] ?? '')));
            $hargaRaw = $row[2] ?? '';

            if (empty($kodePart) || $hargaRaw === '') {
                $invalid++;
                continue;
            }

            // Lookup Rack in iseki_scan (podium connection)
            $rack = Rack::where('Code_Item_Rack', $kodePart)->first();
            if (!$rack) {
                $notFound++;
                continue;
            }

            $noRak = $rack->Code_Rack;
            $namaPart = $rack->Name_Item_Rack;

            // Parse harga: Get raw number or fallback to robust string parsing
            if (is_numeric($hargaRaw)) {
                $harga = (float) $hargaRaw;
            } else {
                $hargaStr = (string) $hargaRaw;
                // Bersihkan simbol mata uang & spasi
                $hargaStr = preg_replace('/[^\d\.,\-]/', '', $hargaStr);
                
                $lastComma = strrpos($hargaStr, ',');
                $lastDot = strrpos($hargaStr, '.');
                
                if ($lastComma !== false && $lastDot !== false) {
                    if ($lastComma > $lastDot) {
                        // Format Indonesia: 1.500.000,50 -> 1500000.50
                        $hargaStr = str_replace('.', '', $hargaStr);
                        $hargaStr = str_replace(',', '.', $hargaStr);
                    } else {
                        // Format US: 1,500,000.50 -> 1500000.50
                        $hargaStr = str_replace(',', '', $hargaStr);
                    }
                } elseif ($lastComma !== false) {
                    if (substr_count($hargaStr, ',') > 1) {
                        // Koma ganda (ribuan): 1,500,000 -> 1500000
                        $hargaStr = str_replace(',', '', $hargaStr);
                    } else {
                        // Satu koma: kita asumsikan koma desimal Indonesia (misal: 150,5)
                        $hargaStr = str_replace(',', '.', $hargaStr);
                    }
                } elseif ($lastDot !== false) {
                    if (substr_count($hargaStr, '.') > 1) {
                        // Titik ganda (ribuan Indonesia): 1.500.000 -> 1500000
                        $hargaStr = str_replace('.', '', $hargaStr);
                    }
                }
                
                $harga = (float) $hargaStr;
            }

            $hargaAsli = $harga;

            // Apply currency conversion to USD
            // hargaAsli = harga original sebelum konversi (disimpan ke DB)
            if (in_array($currency, ['IDR', 'RUPIAH', 'RP'])) {
                $harga = $hargaAsli / 16000;
            } elseif (in_array($currency, ['YEN', 'JPY'])) {
                $harga = $hargaAsli / 140;
            } else {
                // USD — harga sudah dalam USD, langsung dipakai
                $harga = $hargaAsli;
            }

            // Sanity check: skip jika harga konversi tidak masuk akal
            if ($harga <= 0 || $harga > 1000000) {
                $invalid++;
                continue;
            }

            $existing = Pricelist::where(function ($query) use ($kodePart, $noRak, $namaPart) {
                $query->where('kode_part', $kodePart)
                      ->orWhere('no_rak', $noRak)
                      ->orWhere('nama_part', $namaPart);
            })->first();

            if ($existing) {
                if ($existing->kode_part === $kodePart &&
                    $existing->no_rak === $noRak &&
                    $existing->nama_part === $namaPart &&
                    $existing->harga == $harga &&
                    $existing->currency === $currency) {
                    $skipped++;
                    continue;
                }

                $existing->update([
                    'kode_part'  => $kodePart,
                    'no_rak'     => $noRak,
                    'nama_part'  => $namaPart,
                    'harga'      => $harga,
                    'harga_asli' => $hargaAsli,
                    'currency'   => $currency,
                ]);
                $updated++;
            } else {
                $maxNo = Pricelist::max('no') ?? 0;
                Pricelist::create([
                    'no'         => $maxNo + 1,
                    'no_rak'     => $noRak,
                    'kode_part'  => $kodePart,
                    'nama_part'  => $namaPart,
                    'harga'      => $harga,
                    'harga_asli' => $hargaAsli,
                    'currency'   => $currency,
                ]);
                $imported++;
            }
        }

        $msg = "Import selesai. {$imported} data baru ditambahkan";
        if ($updated > 0) $msg .= ", {$updated} data diperbarui";
        if ($skipped > 0) $msg .= ", {$skipped} data sama (dilewati)";
        if ($invalid > 0) $msg .= ", {$invalid} data tidak valid";
        if ($notFound > 0) $msg .= ", {$notFound} part tidak ditemukan di database Rack (di-skip)";
        $msg .= ".";

        return redirect()->route('admin.pricelist.index')
            ->with('success', $msg);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'no_rak'    => 'nullable|string',
            'kode_part' => 'required|string',
            'nama_part' => 'required|string',
            'harga_asli'=> 'required|numeric',
            'currency'  => 'required|string',
        ]);

        $pricelist = Pricelist::findOrFail($id);

        $hargaAsli = (float) $request->harga_asli;
        $currency = strtoupper($request->currency);
        $harga = $hargaAsli;

        if (in_array($currency, ['IDR', 'RUPIAH'])) {
            $harga = $hargaAsli / 16000;
        } elseif (in_array($currency, ['YEN', 'JPY'])) {
            $harga = $hargaAsli * 140;
        } else {
            if ($harga > 1000000) {
                $harga = $harga / 16000;
            }
        }

        $pricelist->update([
            'no_rak'    => $request->no_rak,
            'kode_part' => $request->kode_part,
            'nama_part' => $request->nama_part,
            'harga'     => $harga,
            'harga_asli'=> $hargaAsli,
            'currency'  => $currency,
        ]);

        return redirect()->route('admin.pricelist.index')->with('success', 'Data pricelist berhasil diperbarui.');
    }
}
