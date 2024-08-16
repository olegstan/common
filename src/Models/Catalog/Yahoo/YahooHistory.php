<?php

namespace Common\Models\Catalog\Yahoo;

use Cache;
use Carbon\Carbon;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogHistoryInterface;

/**
 * @property $symbol
 * @property $date
 * @property $open
 * @property $high
 * @property $low
 * @property $close
 * @property $adj_close
 * @property $volume
 */
class YahooHistory extends BaseCatalog implements CommonsFuncCatalogHistoryInterface
{
    /**
     * @var string
     */
    public $table = 'yahoo_history';

    /**
     * @var array
     */
    protected $fillable = [
        'date',
        'open',
        'high',
        'low',
        'close',
        'adj_close',
        'volume',
        'yahoo_stock_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'open' => 'double',
        'high' => 'double',
        'low' => 'double',
        'close' => 'double',
        'adj_close' => 'double',
        'volume' => 'double',
        'yahoo_stock_id' => 'integer',
    ];

    public $timestamps = false;

    /**
     * @param $query
     * @param YahooStock $item
     * @return void
     */
    public function scopeByItem($query, YahooStock $item)
    {
        $query->where('symbol', $item->symbol);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->close;
    }

    /**
     * @return string
     */
    public function getDateField(): string
    {
        return 'date';
    }

    /**
     * @return string
     */
    public function getStockIdField(): string
    {
        return 'yahoo_stock_id';
    }

    /**
     * @param $priceKey
     * @param $dateKey
     * @param $catalog
     * @return array
     */
    public function setPrice($priceKey, $dateKey, $catalog)
    {
        $price = $this->close;
        $date = $this->date;
        Cache::tags([config('cache.tags')])->forever($priceKey, $price);
        Cache::tags([config('cache.tags')])->forever($dateKey, $date && $date instanceof Carbon ? $date->format('Y-m-d') : null);
        return [$priceKey, $price, $date, null, 'yahoo'];
    }

    /**
     * @return mixed
     */
    public function getClose()
    {
        return $this->close;
    }

    /**
     * @return mixed
     */
    public function getOpen()
    {
        return $this->open;
    }

    /**
     * @return mixed
     */
    public function getHigh()
    {
        return $this->high;
    }

    /**
     * @return mixed
     */
    public function getLow()
    {
        return $this->low;
    }

    /**
     * @return mixed
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return $this->date;
    }
}
