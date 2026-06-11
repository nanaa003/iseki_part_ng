<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PartNg;
use App\Models\Pricelist;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Cache priceMap agar tidak query Pricelist::all() berulang kali
     * dalam satu request maupun antar request (TTL 10 menit).
     */
    private function getPriceMap(): array
    {
        return Cache::remember('pricelist_map', 600, function () {
            $map = [];
            foreach (Pricelist::all() as $p) {
                if ($p->kode_part) $map[$p->kode_part] = $p->harga_usd;
                if ($p->no_rak)    $map[$p->no_rak]    = $p->harga_usd;
            }
            return $map;
        });
    }

    /**
     * Hitung cost dan lampirkan ke setiap part sebagai properti sementara.
     */
    private function attachCost($parts): float
    {
        $priceMap  = $this->getPriceMap();
        $totalCost = 0.0;

        foreach ($parts as $part) {
            // Prioritas: gunakan harga_snapshot (dilock saat input)
            // Fallback: cari dari pricelist terkini
            if ($part->harga_snapshot !== null) {
                $harga = (float) $part->harga_snapshot;
            } else {
                $harga = $priceMap[$part->Code_Item_Rack] ?? $priceMap[$part->Code_Rack] ?? 0;
            }
            $part->harga_satuan = $harga;
            $part->cost         = $harga * $part->Total_Part_Ng;
            $totalCost         += $part->cost;
        }

        return $totalCost;
    }

    public function index(Request $request)
    {
        $query = PartNg::with('member');

        $filterDate = $request->has('date') && $request->date
            ? $request->date
            : Carbon::today()->format('Y-m-d');

        $query->whereDate('Date_Part_Ng', $filterDate);

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

        $parts = $query->orderBy('Date_Part_Ng', 'desc')->get();
        $totalCost = $this->attachCost($parts);

        return view('area.dashboard', compact('parts', 'filterDate', 'totalCost'));
    }
}

