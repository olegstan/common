<?php
namespace Common\Models\Traits\Catalog\Cbond;

use App\Models\Actives\Active;
use Common\Models\Catalog\Cbond\CbondCoupon;
use Common\Models\Catalog\Cbond\CbondHistory;
use Common\Models\Catalog\StockMapping;
use Common\Models\Catalog\TradingView\TradingViewTicker;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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

    /**
     * @return MorphMany
     */
    public function actives(): MorphMany
    {
        return $this->morphMany(Active::class, 'item');
    }

    /**
     * @return HasOne
     */
    public function mapping(): HasOne
    {
        return $this->hasOne(StockMapping::class, 'cbond_stocks_id');
    }
}
