<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

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

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'user_areas', 'Id_User', 'Id_Area');
    }

    public function getUserAreaIds(): array
    {
        $ids = DB::table('user_areas')
            ->where('Id_User', $this->Id_User)
            ->pluck('Id_Area')
            ->toArray();
        if ($this->Id_Area) {
            $ids[] = $this->Id_Area;
        }
        return array_unique($ids);
    }

    public function hasAreaRestriction(): bool
    {
        return !empty($this->getUserAreaIds());
    }

    public function applyAreaFilter($query): void
    {
        $areaIds = $this->getUserAreaIds();
        if (!empty($areaIds)) {
            $areaKeys = \App\Models\Area::whereIn('Id_Area', $areaIds)
                ->get(['Divisi', 'Proses']);
            $query->where(function ($q) use ($areaKeys) {
                foreach ($areaKeys as $ak) {
                    $q->orWhere(function ($q2) use ($ak) {
                        $q2->where('Divisi', $ak->Divisi)
                            ->where('proses', $ak->Proses);
                    });
                }
            });
        }
    }
}
