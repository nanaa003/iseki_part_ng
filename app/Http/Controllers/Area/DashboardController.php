<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PartNg;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = PartNg::with('member');
        $user = auth()->user();
        if ($user && $user->hasAreaRestriction()) {
            $user->applyAreaFilter($query);
        }

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

    public function edit($id)
    {
        $part = PartNg::findOrFail($id);

        // Area hanya bisa edit jika belum diproses
        if ($part->penanggungjawab) {
            return redirect()->back()->with('error', 'Tidak bisa mengedit data yang sudah diproses.');
        }

        return view('area.edit', compact('part'));
    }

    public function update(Request $request, $id)
    {
        $part = PartNg::findOrFail($id);

        // Area hanya bisa update jika belum diproses
        if ($part->penanggungjawab) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak bisa mengupdate data yang sudah diproses.']);
            }
            return redirect()->back()->with('error', 'Tidak bisa mengupdate data yang sudah diproses.');
        }

        $request->validate([
            'Desc_Part_Ng' => 'required|string',
            'Category_Part_Ng' => 'required|string',
            'Total_Part_Ng' => 'required|integer|min:1',
            'photo' => 'nullable|image|max:5120',
            'photo_2' => 'nullable|image|max:5120',
            'photo_3' => 'nullable|image|max:5120',
        ]);

        $data = $request->only([
            'Desc_Part_Ng', 'Category_Part_Ng', 'Total_Part_Ng'
        ]);

        $data['Date_Part_Ng'] = DB::raw('Date_Part_Ng');

        // Handle photo uploads
        for ($i = 1; $i <= 3; $i++) {
            $field = $i === 1 ? 'photo' : 'photo_' . $i;
            $dbField = $i === 1 ? 'Photo_Path_Part_Ng' : ($i === 2 ? 'Photo_Path_Part_Ng_2' : 'Photo_Path_Part_Ng_3');

            if ($request->hasFile($field)) {
                // Hapus foto lama jika ada
                if ($part->$dbField && file_exists(public_path($part->$dbField))) {
                    unlink(public_path($part->$dbField));
                }
                $file = $request->file($field);
                $filename = 'part_ng_' . $id . '_' . $i . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/part_ng'), $filename);
                $data[$dbField] = 'uploads/part_ng/' . $filename;
            }

            // Hapus foto jika ada flag hapus
            if ($request->has('remove_photo_' . $i) && $request->input('remove_photo_' . $i) == '1') {
                if ($part->$dbField && file_exists(public_path($part->$dbField))) {
                    unlink(public_path($part->$dbField));
                }
                $data[$dbField] = null;
            }
        }

        $part->update($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data Part NG berhasil diperbarui.']);
        }

        return redirect()->route('area.dashboard')->with('success', 'Data Part NG berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $part = PartNg::findOrFail($id);

        // Area hanya bisa hapus jika belum diproses
        if ($part->penanggungjawab) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak bisa menghapus data yang sudah diproses.']);
            }
            return redirect()->back()->with('error', 'Tidak bisa menghapus data yang sudah diproses.');
        }

        // Hapus file foto
        for ($i = 1; $i <= 3; $i++) {
            $dbField = $i === 1 ? 'Photo_Path_Part_Ng' : ($i === 2 ? 'Photo_Path_Part_Ng_2' : 'Photo_Path_Part_Ng_3');
            if ($part->$dbField && file_exists(public_path($part->$dbField))) {
                unlink(public_path($part->$dbField));
            }
        }

        $part->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data Part NG berhasil dihapus.']);
        }

        return redirect()->route('area.dashboard')->with('success', 'Data Part NG berhasil dihapus.');
    }
}
