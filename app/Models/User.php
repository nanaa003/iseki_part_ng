<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'Id_User';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Name_User',
        'Username_User',
        'Password_User',
        'Id_Type_User',
        'Id_Area',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'Password_User',
    ];

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->Password_User;
    }

    /**
     * Relationship to TypeUser.
     */
    public function typeUser()
    {
        return $this->belongsTo(TypeUser::class, 'Id_Type_User', 'Id_Type_User');
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->Id_Type_User == 1;
    }

    /**
     * Check if the user is a leader.
     */
    public function isLeader(): bool
    {
        return $this->Id_Type_User == 2;
    }

    /**
     * Check if the user is an area user.
     */
    public function isArea(): bool
    {
        return $this->Id_Type_User == 3;
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'Id_Area', 'Id_Area');
    }
}
