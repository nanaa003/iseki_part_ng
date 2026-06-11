<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';
    protected $primaryKey = 'Id_Area';
    public $timestamps = false;

    protected $fillable = [
        'Name_Area',
        'Divisi',
        'Proses',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'Id_Area');
    }
}
