<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PartNg;
use App\Models\Rack;
use App\Models\Pricelist;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PartNgController extends Controller
{
    private array $areaList = [
        'MAIN LINE', 'PAINTING A', 'PAINTING B', 'SUB ASSY',
        'SUB ENGINE', 'MOWER', 'COLLECTOR', 'TRANSMISI', 'DST',
    ];

    public function create()
    {
        return view('admin.input');
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
            'Id_Rack'          => 'required',
            'Code_Rack'        => 'required',
            'Code_Item_Rack'   => 'required',
            'Name_Item_Rack'   => 'required',
            'Desc_Part_Ng'     => 'required',
            'Category_Part_Ng' => 'required',
            'Total_Part_Ng'    => 'required|numeric|min:1',
            'area'             => 'nullable|string',
        ]);

        try {
            // Auto-detect area from user if not provided
            $area = $request->area;
            if (!$area) {
                $user = auth()->user();
                if ($user && $user->Id_Area) {
                    $userArea = \App\Models\Area::find($user->Id_Area);
                    if ($userArea) {
                        $area = $userArea->Name_Area;
                    }
                }
            }

            if ($area) {
                $area = strtoupper(trim($area));
            }

            $divisi = $area;
            $proses = $area;

            [$photoPath, $photoPath2, $photoPath3] = $this->processPhotos($request);

            PartNg::create([
                'Id_Member'            => $request->Id_Member,
                'Id_Rack'              => $request->Id_Rack,
                'Code_Rack'            => $request->Code_Rack,
                'Code_Item_Rack'       => $request->Code_Item_Rack,
                'Name_Item_Rack'       => $request->Name_Item_Rack,
                'Desc_Part_Ng'         => $request->Desc_Part_Ng,
                'Category_Part_Ng'     => $request->Category_Part_Ng,
                'proses'               => $proses,
                'Divisi'               => $divisi,
                'Total_Part_Ng'        => $request->Total_Part_Ng,
                'Photo_Path_Part_Ng'   => $photoPath,
                'Photo_Path_Part_Ng_2' => $photoPath2,
                'Photo_Path_Part_Ng_3' => $photoPath3,
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

    private function processPhotos(Request $request): array
    {
        $photos = [];

        $fields = [
            ['base64' => 'photoData',   'file' => 'photo'],
            ['base64' => 'photoData_2', 'file' => 'photo_2'],
            ['base64' => 'photoData_3', 'file' => 'photo_3'],
        ];

        foreach ($fields as $f) {
            $path = null;

            if ($request->filled($f['base64'])) {
                $raw   = $request->input($f['base64']);
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
            } elseif ($request->hasFile($f['file']) && $request->file($f['file'])->isValid()) {
                $path = 'part_ng-photos/' . $request->file($f['file'])->store('', 'uploads');
            }

            $photos[] = $path;
        }

        return $photos;
    }
}
