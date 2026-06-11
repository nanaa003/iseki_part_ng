<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $connection = 'rifa';
    protected $table = 'employees';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nama',
        'nik',
        'team',
        'division_id',
        'status'
    ];
}
