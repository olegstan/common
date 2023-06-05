<?php

namespace Common\Models\Catalog\MoscowExchange;

use Common\Models\BaseModel;

/**
 * @property $tradedate
 * @property $secid
 * @property $numtrades
 * @property $value
 * @property $open
 * @property $low
 * @property $high
 * @property $close
 * @property $volume
 * @property $warprice
 * @property $marketprice2
 * @property $marketprice3
 * @property $admitquote
 * @property $mp2vatrd
 * @property $marketprice3tradesvalue
 * @property $waval
 * @property $faceunit
 */
class MoscowExchangeHistory extends BaseModel
{
    /**
     * @var string
     */
    public $table = 'moscow_exchange_history';

    /**
     * @var array
     */
    protected $fillable = [
        'tradedate',
        'secid',
        'numtrades',
        'value',
        'open',
        'low',
        'high',
        'close',
        'volume',
        'warprice',
        'marketprice2',
        'marketprice3',
        'admitquote',
        'mp2vatrd',
        'marketprice3tradesvalue',
        'waval',
        'faceunit',
    ];
    /**
     * @var array
     */
    protected $casts = [
        'tradedate' => 'datetime',
        'secid' => 'string',
        'numtrades' => 'float',
        'value' => 'float',
        'open' => 'float',
        'low' => 'float',
        'high' => 'float',
        'close' => 'float',
        'volume' => 'float',
        'warprice' => 'float',
        'marketprice2' => 'float',
        'marketprice3' => 'float',
        'admitquote' => 'float',
        'mp2vatrd' => 'float',
        'marketprice3tradesvalue' => 'float',
        'waval' => 'float',
        'faceunit' => 'string',
    ];

    public $timestamps = false;

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->close;
    }

    /**
     * @param $query
     * @param MoscowExchangeStock $item
     * @return void
     */
    public function scopeByItem($query, MoscowExchangeStock $item)
    {
        $query->where('secid', $item->secid);
    }
}
