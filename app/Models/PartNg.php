<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartNg extends Model
{
    protected $table = 'part_ng';
    protected $primaryKey = 'Id_Part_Ng';
    public $timestamps = false;

    protected $fillable = [
        'Id_Member',
        'Id_Rack',
        'Code_Rack',
        'Code_Item_Rack',
        'Name_Item_Rack',
        'Desc_Part_Ng',
        'Photo_Path_Part_Ng',
        'Photo_Path_Part_Ng_2',
        'Photo_Path_Part_Ng_3',
        'Date_Part_Ng',
        'Category_Part_Ng',
        'proses',
        'Divisi',
        'Total_Part_Ng',
        'penanggungjawab',
        'penyebab',
        'penanganan'
    ];
    
    public function member()
    {
        return $this->belongsTo(Member::class, 'Id_Member', 'id');
    }
}
