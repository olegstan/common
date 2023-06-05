<?php
namespace App\src\Models\Traits\Catalog\Custom;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait CustomRelationshipsTrait
{
    /**
     * @return HasMany
     */
    public function coupons(): HasMany
    {
        //fake для совместимости запросов
        return $this->hasMany(self::class, 'id')->where('id', '<', 0);
    }

    /**
     * @return HasMany
     */
    public function dividends(): HasMany
    {
        //fake для совместимости запросов
        return $this->hasMany(self::class, 'id')->where('id', '<', 0);
    }

    /**
     * @return HasOne
     */
    public function tradingview(): HasOne
    {
        //fake для совместимости запросов
        return $this->hasOne(self::class, 'id')->where('id', '<', 0);
    }
}
