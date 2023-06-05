<?php
namespace App\src\Models\Traits\Catalog\Cbond;

use App\src\Models\Catalog\Cbond\CbondHistory;
use App\src\Models\Catalog\TradingView\TradingViewTicker;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait CbondRelationshipsTrait
{
    /**
     * @return HasOne
     */
    public function tradingview(): HasOne
    {
        return $this->hasOne(TradingViewTicker::class, 'symbol', 'secid');
    }

    /**
     * @return HasOne
     * @deprecated
     */
    public function history(): HasOne
    {
        return $this->hasOne(\App\src\Models\Catalog\Cbond\CbondHistory::class, 'cbond_stock_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(\App\src\Models\Catalog\Cbond\CbondCoupon::class, 'cbond_stock_id')
            ->orderBy('coupondate', 'ASC');
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
    public function histories(): HasMany
    {
        return $this->hasMany(CbondHistory::class, 'cbond_stock_id')
            ->orderBy('tradedate', 'ASC');
    }
}
