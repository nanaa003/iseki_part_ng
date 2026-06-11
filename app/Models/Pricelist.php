<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pricelist extends Model
{
    protected $table = 'pricelists';

    protected $fillable = [
        'kode_part',
        'harga',
        'harga_asli',
        'currency',
    ];

    public function getHargaUsdAttribute()
    {
        return $this->harga;
    }
}
