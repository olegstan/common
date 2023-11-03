<?php

namespace Common\Models\Catalog\MoscowExchange;

use Carbon\Carbon;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Currency as Cur;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogHistoryInterface;
use Cache;

/**
 * @property Carbon $tradedate
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
class MoscowExchangeHistory extends BaseCatalog implements CommonsFuncCatalogHistoryInterface
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
        'moex_stock_id',
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
        'moex_stock_id' => 'integer',
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

    /**
     * @param $priceKey
     * @param $dateKey
     * @return array
     */
    public function setPrice($priceKey, $dateKey)
    {
        if ($this->faceunit === Cur::RUB) {
            $price = $this->close;
            $date = $this->tradedate;
            Cache::forever($priceKey, $price);
            Cache::forever($dateKey, $date && $date instanceof Carbon ? $date->format('Y-m-d') : null);
            return [$priceKey, $price, $date];
        }

        /**
         * @var Cur $convertCurrency
         */
        $convertCurrency = Cache::rememberForever('currency.' . Cur::RUB_ID, function () {
            return Cur::where('id', Cur::RUB_ID)
                ->with('cb_currency')
                ->first();
        });

        if ($convertCurrency) {
            $price = $this->close;
            $date = $this->tradedate;
            $convertedPrice = $convertCurrency->convert(
                $price,
                Cur::getByCode($this->faceunit)->id,
                Carbon::now()
            );

            Cache::forever($priceKey, $convertedPrice);
            Cache::forever($dateKey, $date && $date instanceof Carbon ? $date->format('Y-m-d') : null);
            return [$priceKey, $convertedPrice, $date, $price];
        }
    }
}
