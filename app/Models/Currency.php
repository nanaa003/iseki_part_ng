<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'conversion_type',
        'conversion_rate',
        'is_base',
    ];

    public function convertToBase(float $amount): float
    {
        return $this->conversion_type === 'divide'
            ? $amount / $this->conversion_rate
            : $amount * $this->conversion_rate;
    }

    public static function getBaseCurrency(): self
    {
        return static::where('is_base', true)->first();
    }
}
