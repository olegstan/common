<?php

namespace Common\Models\Catalog\Yahoo;

use Common\Models\Catalog\BaseCatalog;

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
class YahooHistory extends BaseCatalog
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
}
