<?php
namespace App\src\Models\Traits\Catalog\MoscowExchange;

use App\src\Models\Catalog\MoscowExchange\MoscowExchangeCoupon;
use App\src\Models\Catalog\TradingView\TradingViewTicker;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
    public function dividends(): HasMany
    {
        return $this->hasMany(\App\src\Models\Catalog\MoscowExchange\MoscowExchangeDividend::class, 'moex_stock_id')
            ->orderBy('registryclosedate', 'ASC');
    }
}
