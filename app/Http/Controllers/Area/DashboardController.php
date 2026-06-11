<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PartNg;
use Carbon\Carbon;

class DashboardController extends Controller
{
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

        return view('area.dashboard', compact('parts', 'filterDate'));
    }
}
