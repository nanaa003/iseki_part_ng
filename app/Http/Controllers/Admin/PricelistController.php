<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ImportPricelistJob;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Pricelist;

class PricelistController extends Controller
{
    /**
     * Currency yang diizinkan dan kurs konversi ke USD.
     * Untuk mengubah kurs, cukup ubah angka di sini.
     */
    private const EXCHANGE_RATES = [
        'USD'     => 1,
        'IDR'     => 16000,  // 1 USD = 16000 IDR -> harga_idr / 16000 = harga_usd
        'RUPIAH'  => 16000,
        'RP'      => 16000,
        'YEN'     => 140,    // 1 USD = 140 YEN  -> harga_yen / 140 = harga_usd
        'JPY'     => 140,
    ];

    public function index(Request $request)
    {
        // Retrieve any import completion message stored in cache by the background job.
        if (Cache::has('pricelist_import_status')) {
            // Pull removes it from cache after retrieving.
            $msg = Cache::pull('pricelist_import_status');
            session()->flash('success', $msg);
        }

        $search = $request->input('search');

        $pricelists = Pricelist::leftJoin('racks', 'pricelists.kode_part', '=', 'racks.Code_Item_Rack')
            ->when($search, function ($q) use ($search) {
                $q->where('pricelists.kode_part', 'like', "%$search%")
                  ->orWhere('racks.Code_Rack', 'like', "%$search%")
                  ->orWhere('racks.Name_Item_Rack', 'like', "%$search%");
            })
            ->select('pricelists.*', 'racks.Code_Rack as no_rak', 'racks.Name_Item_Rack as nama_item')
            ->orderBy('pricelists.id')
            ->paginate(100)
            ->withQueryString();

        return view('admin.pricelist.index', compact('pricelists', 'search'));
    }

    public function importForm()
    {
        return view('admin.pricelist.import');
    }

    /**
     * Import pricelist dari Excel.
     * Format kolom TETAP (sesuai admin.pricelist.import view):
     *   A = kode part
     *   B = currency (USD / IDR / YEN / dst)
     *   C = harga (dalam currency aslinya)
     * Baris 1 = header, dilewati.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('file');
        // Store temporarily in storage/app/imports
        $path = $file->storeAs('imports', uniqid('pricelist_') . '.' . $file->getClientOriginalExtension());

        // Dispatch background job (absolute path required)
        ImportPricelistJob::dispatch(storage_path('app/' . $path));

        // Immediate user feedback
        return redirect()->route('admin.pricelist.index')
            ->with('success', 'Import dijadwalkan di background. Anda akan menerima notifikasi setelah selesai.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_part'  => 'required|string',
            'harga_asli' => 'required|numeric',
            'currency'   => 'required|string',
        ]);

        $pricelist = Pricelist::findOrFail($id);

        $hargaAsli = (float) $request->harga_asli;
        $currency  = strtoupper($request->currency);

        if (!array_key_exists($currency, self::EXCHANGE_RATES)) {
            return back()->withErrors(['currency' => 'Currency tidak dikenali. Gunakan USD, IDR, atau YEN.']);
        }

        $harga = $this->convertToUsd($hargaAsli, $currency);

        $pricelist->update([
            'kode_part'  => $request->kode_part,
            'harga'      => $harga,
            'harga_asli' => $hargaAsli,
            'currency'   => $currency,
        ]);

        return redirect()->route('admin.pricelist.index')->with('success', 'Data pricelist berhasil diperbarui.');
    }

    /**
     * Konversi harga dari currency asli ke USD.
     * Satu-satunya tempat logika konversi didefinisikan —
     * dipakai oleh importExcel() dan update() agar konsisten.
     */
    private function convertToUsd(float $hargaAsli, string $currency): float
    {
        $currency = strtoupper(trim($currency));
        $rate = self::EXCHANGE_RATES[$currency] ?? 1;

        return $rate == 1 ? $hargaAsli : $hargaAsli / $rate;
    }

    /**
     * Parse nilai harga dari Excel: dukung angka mentah maupun string
     * dengan format ribuan/desimal Indonesia atau US.
     * Mengembalikan null jika tidak bisa diparse sebagai angka.
     */
    private function parseHarga(mixed $hargaRaw): ?float
    {
        if (is_numeric($hargaRaw)) {
            return (float) $hargaRaw;
        }

        $str = trim((string) $hargaRaw);
        $str = preg_replace('/[^\d.,\-]/', '', $str);

        if ($str === '') return null;

        $lastComma = strrpos($str, ',');
        $lastDot   = strrpos($str, '.');

        if ($lastComma !== false && $lastDot !== false) {
            if ($lastComma > $lastDot) {
                // Format Indonesia: 1.500.000,50 -> 1500000.50
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                // Format US: 1,500,000.50 -> 1500000.50
                $str = str_replace(',', '', $str);
            }
        } elseif ($lastComma !== false) {
            if (substr_count($str, ',') > 1) {
                $str = str_replace(',', '', $str);
            } else {
                $str = str_replace(',', '.', $str);
            }
        } elseif ($lastDot !== false) {
            if (substr_count($str, '.') > 1) {
                $str = str_replace('.', '', $str);
            }
        }

        return is_numeric($str) ? (float) $str : null;
    }
}
