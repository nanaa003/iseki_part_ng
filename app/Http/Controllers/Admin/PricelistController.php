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

        $currencies = Pricelist::distinct()->orderBy('currency')->pluck('currency');

        return view('admin.pricelist.index', compact('pricelists', 'currencies'));
    }

    public function importForm()
    {
        return view('admin.pricelist.import');
    }

    public function importExcel(Request $request)
    {
        set_time_limit(300);

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

            // Column order: kode_part, currency, harga
            $kodePart = trim($row[0] ?? '');
            $currency = strtoupper(trim($row[1] ?? ''));
            $hargaRaw = trim($row[2] ?? '');

            if (empty($kodePart) || $hargaRaw === '') {
                $invalid++;
                continue;
            }

            // Lookup rack dari database iseki_scan
            $rack = Rack::where('Code_Item_Rack', $kodePart)->first();
            if (!$rack) {
                $invalid++;
                continue;
            }
            $noRak    = $rack->Code_Rack;
            $namaPart = $rack->Name_Item_Rack;

            // Parse harga: handle Indonesian & US number formats
            $harga = $this->parseNumber($hargaRaw);
            $hargaAsli = $harga;

            // Apply currency conversion to USD
            if (in_array($currency, ['IDR', 'RUPIAH'])) {
                if ($harga > 1000000) {
                    $invalid++;
                    continue;
                }
                $harga = $harga / 16000;
            } elseif (in_array($currency, ['YEN', 'JPY'])) {
                $harga = $harga * 140;
            }

            // Skip jika hasil konversi tidak wajar (> 1 juta USD per part)
            if ($harga <= 0 || $harga > 1000000) {
                $invalid++;
                continue;
            }

            $existing = Pricelist::where('kode_part', $kodePart)->first();

            if ($existing) {
                if ($existing->harga == $harga &&
                    $existing->currency === $currency) {
                    $skipped++;
                    continue;
                }

                $existing->update([
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

    private function parseNumber(string $value): float
    {
        $value = trim($value);
        if ($value === '') return 0;

        // Cek apakah pakai koma sebagai desimal (format Indonesia)
        if (str_contains($value, ',')) {
            // Hapus titik (ribuan), ganti koma jadi titik (desimal)
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            // Multiple dots → ribuan (format Indonesia tanpa koma), hapus semua
            if (substr_count($value, '.') > 1) {
                $value = str_replace('.', '', $value);
            }
            // Single dot atau tanpa dot → biarkan (format US atau integer)
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
