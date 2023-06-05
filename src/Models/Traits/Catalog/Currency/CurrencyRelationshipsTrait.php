<?php
namespace App\src\Models\Traits\Catalog\Currency;

use App\src\Models\Currency;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
        return $this->hasOne(\App\src\Models\Catalog\Currency\CbHistoryCurrencyCourse::class, 'currency_id')->latest('date');
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
