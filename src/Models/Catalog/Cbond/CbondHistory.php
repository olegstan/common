<?php

namespace Common\Models\Catalog\Cbond;

use Common\Models\Catalog\BaseCatalog;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property $cbond_stock_id
 * @property $tradedate
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
class CbondHistory extends BaseCatalog
{
    /**
     * @var string
     */
    public $table = 'cbond_history';

    /**
     * @var array
     */
    protected $fillable = [
        'cbond_stock_id',
        'tradedate',
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
        'cbond_stock_id' => 'integer',
        'tradedate' => 'datetime',
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
     * @return HasOne
     */
    public function item(): HasOne
    {
        return $this->hasOne(CbondStock::class, 'cbond_stock_id');
    }

    /**
     * @param $query
     * @param CbondStock $item
     * @return void
     */
    public function scopeByItem($query, CbondStock $item)
    {
        $query->where('isin', $item->isin);
    }
}
