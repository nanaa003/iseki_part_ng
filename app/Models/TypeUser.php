<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeUser extends Model
{
    protected $table = 'type_users';
    protected $primaryKey = 'Id_Type_User';
    public $timestamps = false;

    protected $fillable = [
        'Name_Type_User',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'Id_Type_User', 'Id_Type_User');
    }
}
