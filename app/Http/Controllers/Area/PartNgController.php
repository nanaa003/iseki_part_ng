<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PartNg;
use App\Models\Member;
use App\Models\Rack;
use App\Models\Pricelist;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PartNgController extends Controller
{
    public function index(Request $request)
    {
        $query = PartNg::with('member');

        if ($request->has('area') && $request->area) {
            $query->where('Divisi', $request->area);
        }

        $filterDate = $request->has('date') && $request->date
            ? $request->date
            : Carbon::today()->format('Y-m-d');

        $query->whereDate('Date_Part_Ng', $filterDate);

        if ($request->has('category') && $request->category) {
            $query->where('Category_Part_Ng', $request->category);
        }

        $parts = $query->orderBy('Date_Part_Ng', 'desc')->get();

        $totalCost = 0;
        $pricelists = Pricelist::all();
        $priceMap = [];
        foreach ($pricelists as $p) {
            if ($p->kode_part) $priceMap[$p->kode_part] = $p->harga_usd;
            if ($p->no_rak)    $priceMap[$p->no_rak]    = $p->harga_usd;
        }
        foreach ($parts as $part) {
            $harga = $priceMap[$part->Code_Item_Rack] ?? $priceMap[$part->Code_Rack] ?? 0;
            $part->cost = $harga * $part->Total_Part_Ng;
            $totalCost += $part->cost;
        }

        $areas = DB::table('areas')->orderBy('name')->pluck('name');

        return view('area.index', compact('parts', 'filterDate', 'totalCost', 'areas'));
    }

    public function create()
    {
        $area = null;
        $user = auth()->user();

        if ($user) {
            $area = $this->getAreaFromUser($user);

            if ($user->isAdmin()) {
                return view('admin.input', compact('area'));
            }

            if ($user->isLeader()) {
                return view('leader.input', compact('area'));
            }
        }

        return view('area.input', compact('area'));
    }

    public function verifyRack(Request $request)
    {
        try {
            $code = $request->input('qr_data');

            if (!$code) {
                return response()->json(['success' => false, 'message' => 'Data Rack kosong']);
            }

            $rack = Rack::where('Code_Rack', $code)->first();

            if (!$rack) {
                return response()->json(['success' => false, 'message' => 'Data Rack tidak ditemukan']);
            }

            $price = 0;
            $pricelist = Pricelist::where('kode_part', $rack->Code_Item_Rack)
                ->orWhere('no_rak', $rack->Code_Rack)
                ->first();
            if ($pricelist) $price = $pricelist->harga_usd;

            return response()->json([
                'success'        => true,
                'id_rack'        => $rack->Id_Rack,
                'code_rack'      => $rack->Code_Rack,
                'code_item_rack' => $rack->Code_Item_Rack,
                'name_item_rack' => $rack->Name_Item_Rack,
                'harga'          => $price,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'Id_Rack'        => 'required',
            'Code_Rack'      => 'required',
            'Code_Item_Rack' => 'required',
            'Name_Item_Rack' => 'required',
            'Desc_Part_Ng'   => 'required',
            'Category_Part_Ng'=> 'required',
            'Total_Part_Ng'  => 'required|numeric|min:1',
        ]);

        try {
            $photos = [];
            $fields = [
                ['input' => 'photo',   'db' => 'Photo_Path_Part_Ng'],
                ['input' => 'photo_2', 'db' => 'Photo_Path_Part_Ng_2'],
                ['input' => 'photo_3', 'db' => 'Photo_Path_Part_Ng_3'],
            ];
            foreach ($fields as $f) {
                $path = null;
                $file = $request->file($f['input']);
                if ($file) {
                    if ($file->isValid()) {
                        $path = 'part_ng-photos/' . $file->store('', 'uploads');
                    } else {
                        throw new \Exception('Foto (' . $f['input'] . ') tidak valid. Pastikan ukuran file tidak terlalu besar.');
                    }
                } elseif ($request->filled($f['input'])) {
                    $raw = $request->input($f['input']);
                    if (is_string($raw) && strpos($raw, ';base64,') !== false) {
                        $parts = explode(';base64,', $raw);
                        if (count($parts) === 2) {
                            $typePart = explode('image/', $parts[0]);
                            $ext      = $typePart[1] ?? 'png';
                            $decoded  = base64_decode($parts[1]);
                            if ($decoded !== false) {
                                $fileName = 'part_ng_photo_' . uniqid() . '.' . $ext;
                                Storage::disk('uploads')->put($fileName, $decoded);
                                $path = 'part_ng-photos/' . $fileName;
                            }
                        }
                    }
                }
                $photos[] = $path;
            }

            $area = null;
            $user = auth()->user();
            if ($user && $user->Id_Area) {
                $area = \App\Models\Area::find($user->Id_Area);
            }

            PartNg::create([
                'Id_Member'            => $request->Id_Member,
                'Id_Rack'              => $request->Id_Rack,
                'Code_Rack'            => $request->Code_Rack,
                'Code_Item_Rack'       => $request->Code_Item_Rack,
                'Name_Item_Rack'       => $request->Name_Item_Rack,
                'Desc_Part_Ng'         => $request->Desc_Part_Ng,
                'Category_Part_Ng'     => $request->Category_Part_Ng,
                'Total_Part_Ng'        => $request->Total_Part_Ng,
                'Divisi'               => $area ? $area->Divisi : ($request->Divisi ?? $request->area),
                'proses'               => $area ? $area->Proses : ($request->proses ?? $request->area),
                'Photo_Path_Part_Ng'   => $photos[0],
                'Photo_Path_Part_Ng_2' => $photos[1],
                'Photo_Path_Part_Ng_3' => $photos[2],
                'Date_Part_Ng'         => Carbon::now()->format('Y-m-d H:i:s'),
                'penanggungjawab'      => null,
                'penyebab'             => null,
                'penanganan'           => null,
            ]);

            return response()->json(['success' => true, 'message' => 'Data berhasil disimpan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan data: ' . $e->getMessage()], 500);
        }
    }

    public function searchMembers(Request $request)
    {
        try {
            $search = $request->input('q', '');

            $members = Member::when($search, function ($q, $search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get(['id', 'nik', 'nama']);

            return response()->json($members);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    private function getAreaFromUser($user)
    {
        if ($user->isAdmin()) return null;

        if ($user->Id_Area) {
            return \App\Models\Area::find($user->Id_Area);
        }

        return null;
    }
}
