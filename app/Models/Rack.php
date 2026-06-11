<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rack extends Model
{
    protected $connection = 'podium';
    protected $table = 'racks';
    protected $primaryKey = 'Id_Rack';
    public $timestamps = false;

    protected $fillable = [
        'Code_Rack',
        'Code_Item_Rack',
        'Name_Item_Rack',
        'Type_Tractor_Rack',
        'Update_Rack'
    ];
}
