<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pricelist extends Model
{
    protected $table = 'pricelists';

    protected $fillable = [
        'no',
        'no_rak',
        'kode_part',
        'nama_part',
        'harga',
        'harga_asli',
        'currency',
    ];

    public function getHargaUsdAttribute()
    {
        return $this->harga;
    }
}
