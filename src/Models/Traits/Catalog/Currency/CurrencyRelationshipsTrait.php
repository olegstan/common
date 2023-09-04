<?php
namespace Common\Models\Traits\Catalog\Currency;

use Common\Models\Catalog\Currency\CbHistoryCurrencyCourse;
use Common\Models\Currency;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait CurrencyRelationshipsTrait
{
    /**
     * @return HasOne
     */
    public function currency(): HasOne
    {
        return $this->hasOne(Currency::class, 'code', 'char_code');
    }

    /**
     * @return HasOne
     */
    public function cb_course(): HasOne
    {
        return $this->hasOne(CbHistoryCurrencyCourse::class, 'currency_id')->latest('date');
    }

    /**
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(CbHistoryCurrencyCourse::class, 'currency_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function tradingview(): HasOne
    {
        //fake для совместимости запросов
        return $this->hasOne(self::class, 'id')
            ->where('id', '<', 0);
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
    public function dividends(): HasMany
    {
        //fake для совместимости запросов
        return $this->hasMany(self::class, 'id')->where('id', '<', 0);
    }
}
