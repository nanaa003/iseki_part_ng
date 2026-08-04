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
        'harga_asli',
        'currency',
    ];

    protected static $currencyCache = null;

    /**
     * Lookup harga hanya berdasarkan kode_part (bukan no_rak).
     * Deterministik: ambil baris dengan id terkecil bila ada duplikat.
     */
    public static function findByKodePart(?string $kode): ?self
    {
        if (!$kode) {
            return null;
        }
        return static::where('kode_part', $kode)->orderBy('id')->first();
    }

    public static function getHargaUsdByKodePart(?string $kode): ?float
    {
        $pricelist = static::findByKodePart($kode);
        return $pricelist ? (float) $pricelist->harga_usd : null;
    }

    protected static function loadCurrencies()
    {
        if (static::$currencyCache === null) {
            static::$currencyCache = Currency::all()->keyBy('code');
        }
        return static::$currencyCache;
    }

    public function getHargaUsdAttribute()
    {
        $currencies = static::loadCurrencies();
        $currency = $currencies->get($this->currency);
        if (!$currency || $currency->is_base) {
            return (float) $this->harga_asli;
        }
        return $currency->convertToBase((float) $this->harga_asli);
    }
}
