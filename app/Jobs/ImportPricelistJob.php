<?php

namespace App\Jobs;

use App\Models\Pricelist;
use App\Models\Currency;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use App\Models\Rack;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class ImportPricelistJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var string */
    public $filePath;
    /** @var int */
    protected $chunkSize = 500;
    /** @var int */
    public $imported = 0;
    /** @var int */
    public $updated = 0;
    /** @var int */
    public $invalid = 0;

    /**
     * Create a new job instance.
     *
     * @param string $filePath  Absolute path to the uploaded Excel file.
     * @param int    $chunkSize Number of rows processed per DB transaction.
     */
    public function __construct(string $filePath, int $chunkSize = 500)
    {
        $this->filePath   = $filePath;
        // Counters for import summary
        $this->imported = 0;
        $this->updated  = 0;
        $this->invalid  = 0;
        $this->chunkSize  = $chunkSize;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Remove PHP execution limits for long imports.
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $spreadsheet = IOFactory::load($this->filePath);
        $worksheet   = $spreadsheet->getActiveSheet();
        $rows        = $worksheet->toArray(null, true, true, true); // keep column letters as keys

        // First row is assumed to be the header.
        $header = array_shift($rows);

        // Map header titles to column letters.
        $colMap = [
            'kode_part' => null,
            'currency'  => null,
            'harga'     => null,
        ];
        foreach ($header as $col => $title) {
            $lower = strtolower(trim($title));
            foreach ($colMap as $key => $val) {
                if ($lower === $key) {
                    $colMap[$key] = $col;
                }
            }
        }
        // Abort if required columns are missing.
        if (in_array(null, $colMap, true)) {
            // Log could be added here.
            return;
        }

        $chunks = array_chunk($rows, $this->chunkSize);
        foreach ($chunks as $chunk) {
            $this->processChunk($chunk, $colMap);
            // Continue to next chunk
        }

        // Clean up the uploaded file.
        // Store a summary message in cache for UI flash
            $msg = "Import selesai. {$this->imported} data baru ditambahkan, {$this->updated} data diperbarui, {$this->invalid} baris tidak valid.";
            Cache::put('pricelist_import_status', $msg, now()->addMinutes(10));
            @unlink($this->filePath);
    }

    /**
     * Process a chunk of rows.
     */
    protected function processChunk(array $chunk, array $colMap): void
    {
        foreach ($chunk as $row) {
            $kodePart = trim($row[$colMap['kode_part']]);
            $currency = strtoupper(trim($row[$colMap['currency']]));
            $hargaRaw = $row[$colMap['harga']];

            $harga = $this->parseHarga($hargaRaw);
            if ($harga === null) {
                $this->invalid++;
                continue; // Skip rows with invalid price.
            }

            $rack = Rack::where('Code_Item_Rack', $kodePart)->first();
            if (!$rack) {
                // No matching rack, skip this row.
                $this->invalid++;
                continue;
            }

            $hargaUsd = $this->convertToUsd($harga, $currency);

            // Upsert based on kode_part.
            $model = Pricelist::updateOrCreate(
                ['kode_part' => $kodePart],
                [
                    'harga'       => $hargaUsd,
                    'harga_asli'  => $harga,
                    'currency'    => $currency,
                    'updated_at'  => Carbon::now(),
                    'created_at'  => Carbon::now(),
                ]
            );
            if ($model->wasRecentlyCreated) {
                $this->imported++;
            } else {
                $this->updated++;
            }
        }
    }

    /**
     * Parse a raw harga value into a float.
     */
    protected function parseHarga($value): ?float
    {
        if (is_null($value)) {
            return null;
        }
        // Remove any non‑numeric characters except dot and comma.
        $clean = preg_replace('/[^0-9.,-]/', '', $value);
        // Normalise decimal separator.
        if (strpos($clean, ',') !== false && strpos($clean, '.') === false) {
            $clean = str_replace(',', '.', $clean);
        } else {
            $clean = str_replace(',', '', $clean);
        }
        return is_numeric($clean) ? (float) $clean : null;
    }

    /**
     * Convert a price to USD based on the given currency.
     */
    protected function convertToUsd(float $harga, string $currency): float
    {
        $code = strtoupper(trim($currency));
        if ($code === 'RUPIAH' || $code === 'RP') {
            $code = 'IDR';
        } elseif ($code === 'YEN') {
            $code = 'JPY';
        }

        $currencyModel = Currency::where('code', $code)->first();
        if ($currencyModel) {
            return round($currencyModel->convertToBase($harga), 2);
        }

        $rates = [
            'IDR' => 1 / 16000,
            'USD' => 1,
            'JPY' => 1 / 140,
        ];
        $rate = $rates[$code] ?? 1;
        return round($harga * $rate, 2);
    }
}
?>
