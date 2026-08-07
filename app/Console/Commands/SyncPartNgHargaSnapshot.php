<?php

namespace App\Console\Commands;

use App\Models\PartNg;
use App\Models\Pricelist;
use Illuminate\Console\Command;

class SyncPartNgHargaSnapshot extends Command
{
    protected $signature = 'part-ng:sync-harga {--dry-run : Hanya tampilkan yang akan diubah, tanpa menyimpan}';

    protected $description = 'Sinkronkan harga_snapshot Part NG lama dengan harga pricelist saat ini (perbaiki harga yang salah/asal).';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('MODE DRY-RUN: tidak ada perubahan yang disimpan.');
        }

        $changed = 0;
        $toNull = 0;

        PartNg::query()
            ->orderBy('Id_Part_Ng')
            ->chunkById(200, function ($parts) use (&$changed, &$toNull, $dryRun) {
                foreach ($parts as $part) {
                    $newHarga = Pricelist::getHargaUsdByKodePart($part->Code_Item_Rack);
                    $oldHarga = $part->harga_snapshot !== null ? (float) $part->harga_snapshot : null;

                    if ($newHarga === null) {
                        if ($part->harga_snapshot !== null) {
                            $this->line("Id {$part->Id_Part_Ng} ({$part->Code_Item_Rack}): harga {$oldHarga} -> tidak ditemukan (snapshot NULL)");
                            $toNull++;
                            $changed++;
                            if (!$dryRun) {
                                $part->harga_snapshot = null;
                                $part->save();
                            }
                        }
                        continue;
                    }

                    if ($oldHarga === null || abs($oldHarga - $newHarga) > 0.01) {
                        $this->line("Id {$part->Id_Part_Ng} ({$part->Code_Item_Rack}): " . ($oldHarga === null ? 'NULL' : $oldHarga) . " -> {$newHarga}");
                        $changed++;
                        if (!$dryRun) {
                            $part->harga_snapshot = $newHarga;
                            $part->save();
                        }
                    }
                }
            });

        $this->info("Selesai. {$changed} record perlu diperbarui ({$toNull} di antaranya menjadi NULL).");
        return self::SUCCESS;
    }
}
