<?php
namespace Common\Models\Traits\Catalog\MoscowExchange;

use Common\Models\Catalog\Cbond\CbondHistory;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeCoupon;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeDividend;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeHistory;
use Common\Models\Catalog\TradingView\TradingViewTicker;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Actives\Active;

trait MoexRelationshipsTrait
{
    /**
     * @return HasOne
     */
    public function tradingview(): HasOne
    {
        return $this->hasOne(TradingViewTicker::class, 'symbol', 'secid')
            ->where('exchange', 'MOEX');
    }

    /**
     * @return HasMany
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(MoscowExchangeCoupon::class, 'moex_stock_id')
            ->orderBy('coupondate', 'ASC');
    }

    /**
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(MoscowExchangeHistory::class, 'moex_stock_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function dividends(): HasMany
    {
        return $this->hasMany(MoscowExchangeDividend::class, 'moex_stock_id')
            ->orderBy('registryclosedate', 'ASC');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function actives()
    {
        return $this->morphMany(Active::class, 'item');
    }
}
