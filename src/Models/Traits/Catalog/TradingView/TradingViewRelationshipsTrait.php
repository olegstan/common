<?php
namespace Common\Models\Traits\Catalog\TradingView;

use Common\Models\Catalog\TradingView\TradingViewChartDay;
use Common\Models\Catalog\TradingView\TradingViewQuarter;
use Common\Models\Catalog\TradingView\TradingViewYear;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait TradingViewRelationshipsTrait
{
    /**
     * @return HasMany
     */
    public function quarterly(): HasMany
    {
        return $this->hasMany(TradingViewQuarter::class, 'ticker_id', 'id')
            ->orderBy('year')
            ->orderBy('quarter');
    }

    /**
     * @return HasMany
     */
    public function yearly(): HasMany
    {
        return $this->hasMany(TradingViewYear::class, 'ticker_id', 'id')
            ->orderByRaw("CASE WHEN year IS NULL THEN 1 ELSE 0 END")
            ->orderBy('year');
    }

    /**
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->chartDays();
    }

    /**
     * @return HasMany
     */
    public function chartDays(): HasMany
    {
        return $this->hasMany(TradingViewChartDay::class, 'ticker_id', 'id');
    }
}
