<?php

namespace Common\Models\Catalog\Custom;

use Cache;
use Carbon\Carbon;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Currency as Cur;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogHistoryInterface;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property $symbol
 * @property $currency
 * @property Carbon $date
 * @property $price
 */
class CustomHistory extends BaseCatalog implements CommonsFuncCatalogHistoryInterface
{
    /**
     * @var string
     */
    public $table = 'custom_history';

    /**
     * @var array
     */
    protected $fillable = [
        'symbol',
        'currency',
        'date',
        'price',
    ];
    /**
     * @var array
     */
    protected $casts = [
        'symbol' => 'string',
        'currency' => 'string',
        'date' => 'date',
        'price' => 'float',
    ];

    public $timestamps = false;

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->price;
    }

    /**
     * @return HasOne
     */
    public function item(): HasOne
    {
        return $this->hasOne(CustomStock::class, 'symbol', 'symbol');
    }

    /**
     * @param $query
     * @param CustomStock $item
     * @return void
     */
    public function scopeByItem($query, CustomStock $item)
    {
        $query->where('symbol', $item->symbol);
    }

    /**
     * @param $priceKey
     * @param $dateKey
     * @return void
     */
    public function setPrice($priceKey, $dateKey)
    {
        //TODO
    }

    /**
     * @return mixed
     */
    public function getClose()
    {
        return $this->price;
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
