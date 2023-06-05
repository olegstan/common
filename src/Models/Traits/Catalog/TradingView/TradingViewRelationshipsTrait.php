<?php
namespace App\src\Models\Traits\Catalog\TradingView;

use App\src\Models\Catalog\TradingView\TradingViewQuarter;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
        return $this->hasMany(\App\src\Models\Catalog\TradingView\TradingViewYear::class, 'ticker_id', 'id')
            ->orderByRaw("CASE WHEN year IS NULL THEN 1 ELSE 0 END")
            ->orderBy('year');
    }

    /**
     * @return HasMany
     */
    public function chartDays(): HasMany
    {
        return $this->hasMany(\App\src\Models\Catalog\TradingView\TradingViewChartDay::class, 'ticker_id', 'id');
    }
}
