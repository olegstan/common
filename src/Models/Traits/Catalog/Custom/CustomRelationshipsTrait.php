<?php
namespace Common\Models\Traits\Catalog\Custom;

use App\Models\Actives\Active;
use Common\Models\Catalog\Custom\CustomHistory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(CustomHistory::class, 'symbol', 'symbol');
    }

    /**
     * @return HasOne
     */
    public function tradingview(): HasOne
    {
        //fake для совместимости запросов
        return $this->hasOne(self::class, 'id')->where('id', '<', 0);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function actives()
    {
        return $this->morphMany(Active::class, 'item');
    }
}
