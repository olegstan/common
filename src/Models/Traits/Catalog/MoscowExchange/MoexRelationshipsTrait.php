<?php
namespace Common\Models\Traits\Catalog\MoscowExchange;

use App\Models\Actives\Active;
use Common\Models\Catalog\Finex\FinexHistory;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeCoupon;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeDividend;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeHistory;
use Common\Models\Catalog\TradingView\TradingViewTicker;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        if (str_contains(strtolower($this->latname), 'finex')) {
            return $this->finexHistory();
        }

        return $this->hasMany(MoscowExchangeHistory::class, 'moex_stock_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function finexHistory(): HasMany
    {
        return $this->hasMany(FinexHistory::class, 'moex_stock_id', 'id');
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
