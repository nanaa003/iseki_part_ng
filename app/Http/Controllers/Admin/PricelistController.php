<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pricelist;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PricelistController extends Controller
{
    public function index()
    {
        $pricelists = Pricelist::orderBy('no')->paginate(100);
        return view('admin.pricelist.index', compact('pricelists'));
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
        $rows = $worksheet->toArray();

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $invalid = 0;

        foreach ($rows as $index => $row) {
            if ($index == 0) continue;

            // Column order: rack code, item code, item name, currency, harga
            $noRak    = trim($row[0] ?? '');
            $kodePart = trim($row[1] ?? '');
            $namaPart = trim($row[2] ?? '');
            $currency = strtoupper(trim($row[3] ?? ''));
            $hargaRaw = trim($row[4] ?? '');

            if (empty($noRak) || empty($kodePart) || empty($namaPart) || $hargaRaw === '') {
                $invalid++;
                continue;
            }

            // Parse harga: remove dots, replace comma with dot
            $harga = (float) str_replace(['.', ','], ['', '.'], $hargaRaw);
            $hargaAsli = $harga;

            // Apply currency conversion to USD
            if (in_array($currency, ['IDR', 'RUPIAH'])) {
                $harga = $harga / 16000;
            } elseif (in_array($currency, ['YEN', 'JPY'])) {
                $harga = $harga * 140;
            } else {
                // USD or unrecognized → if value is absurdly large (> 1 million),
                // it's likely an IDR price mislabeled in Excel → force-convert
                if ($harga > 1000000) {
                    $harga = $harga / 16000;
                }
            }

            // decimal(15,2) max value is ~9,999,999,999,999.99
            // Skip if unreasonable (> 1 million USD for a single part)
            if ($harga <= 0 || $harga > 9999999999999 || $harga > 1000000) {
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
        if ($invalid > 0) $msg .= ", {$invalid} data tidak valid (dilewati)";
        $msg .= ".";

        return redirect()->route('admin.pricelist.index')
            ->with('success', $msg);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_part' => 'required|string',
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
            'kode_part' => $request->kode_part,
            'harga'     => $harga,
            'harga_asli'=> $hargaAsli,
            'currency'  => $currency,
        ]);

        return redirect()->route('admin.pricelist.index')->with('success', 'Data pricelist berhasil diperbarui.');
    }
}
