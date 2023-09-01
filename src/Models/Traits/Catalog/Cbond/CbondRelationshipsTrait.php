<?php
namespace Common\Models\Traits\Catalog\Cbond;

use Common\Models\Catalog\Cbond\CbondCoupon;
use Common\Models\Catalog\Cbond\CbondHistory;
use Common\Models\Catalog\TradingView\TradingViewTicker;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
     * @return HasMany
     * @deprecated
     */
    public function history(): HasMany
    {
        return $this->hasMany(CbondHistory::class, 'cbond_stock_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(CbondCoupon::class, 'cbond_stock_id')
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
