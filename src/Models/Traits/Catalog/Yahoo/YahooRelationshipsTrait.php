<?php
namespace Common\Models\Traits\Catalog\Yahoo;

use App\Models\Actives\Active;
use Common\Models\Catalog\StockMapping;
use Common\Models\Catalog\TradingView\TradingViewTicker;
use Common\Models\Catalog\Yahoo\YahooHistory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait YahooRelationshipsTrait
{
    /**
     * @return HasOne
     */
    public function tradingview(): HasOne
    {
        return $this->hasOne(TradingViewTicker::class, 'symbol', 'symbol')
            ->where('exchange', '!=', 'MOEX');
    }

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
    public function history(): HasMany
    {
        return $this->hasMany(YahooHistory::class, 'yahoo_stock_id', 'id');
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
        return $this->hasOne(StockMapping::class, 'yahoo_stocks_id');
    }
}
