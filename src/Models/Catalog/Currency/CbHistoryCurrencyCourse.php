<?php

namespace Common\Models\Catalog\Currency;

use Cache;
use Carbon\Carbon;
use Common\Helpers\Curls\Currency\CbCurl;
use Common\Helpers\LoggerHelper;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Currency;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogHistoryInterface;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property $currency_id
 * @property $value
 * @property $nominal
 * @property $date
 */
class CbHistoryCurrencyCourse extends BaseCatalog implements CommonsFuncCatalogHistoryInterface
{
    /**
     * @var string
     */
    public $table = 'cb_history_currency_courses';

    /**
     * @var array
     */
    protected $fillable = [
        'currency_id',
        'value',
        'nominal',
        'date',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'currency_id' => 'integer',
        'value' => 'float',
        'nominal' => 'float',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    public static array $codes = [
        Currency::EUR,
        Currency::USD
    ];

    /**
     * @return HasOne
     */
    public function cb_currency(): HasOne
    {
        return $this->hasOne(CbCurrency::class, 'id', 'currency_id');
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @param $query
     * @param CbCurrency $item
     * @return void
     */
    public function scopeByItem($query, CbCurrency $item)
    {
        $query->where('currency_id', $item->id);
    }

    /**
     * @param $priceKey
     * @param $dateKey
     * @param $catalog
     */
    public function setPrice($priceKey, $dateKey, $catalog)
    {

    }

    /**
     * @return mixed
     */
    public function getClose()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getOpen(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getHigh(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getLow(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getVolume(): int
    {
        return 0;
    }

    /**
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return $this->date;
    }
}
