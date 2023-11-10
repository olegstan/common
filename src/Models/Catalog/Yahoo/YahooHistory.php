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
        'symbol',
        'date',
        'open',
        'high',
        'low',
        'close',
        'adj_close',
        'volume',
        'symbol_stock_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'symbol' => 'string',
        'date' => 'date',
        'open' => 'double',
        'high' => 'double',
        'low' => 'double',
        'close' => 'double',
        'adj_close' => 'double',
        'volume' => 'double',
        'symbol_stock_id' => 'integer',
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
     * @param $key
     * @return array
     */
    public function setPrice($priceKey, $dateKey)
    {
        $price = $this->close;
        $date = $this->date;
        Cache::forever($priceKey, $price);
        Cache::forever($dateKey, $date && $date instanceof Carbon ? $date->format('Y-m-d') : null);
        return [$priceKey, $price, $date, null, 'yahoo'];
    }
}
