<?php

namespace Common\Models\Catalog\MoscowExchange;

use Cache;
use Carbon\Carbon;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Currency as Cur;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogHistoryInterface;

/**
 * @property Carbon $tradedate
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
 * @property $moex_stock_id
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
            Cache::tags([config('cache.tags')])->forever($priceKey, $price);
            Cache::tags([config('cache.tags')])->forever($dateKey, $date && $date instanceof Carbon ? $date->format('Y-m-d') : null);
            return [$priceKey, $price, $date, null, 'moex'];
        }

        /**
         * @var Cur $convertCurrency
         */
        $convertCurrency = Cur::getById(Cur::RUB_ID);

        if ($convertCurrency) {
            $price = $this->close;
            $date = $this->tradedate;
            $convertedPrice = $convertCurrency->convert(
                $price,
                Cur::getByCode($this->faceunit)->id,
                Carbon::now()
            );

            Cache::tags([config('cache.tags')])->forever($priceKey, $convertedPrice);
            Cache::tags([config('cache.tags')])->forever($dateKey, $date && $date instanceof Carbon ? $date->format('Y-m-d') : null);
            return [$priceKey, $convertedPrice, $date, $price, 'moex'];
        }
    }

    /**
     * @return string
     */
    public function getDateField(): string
    {
        return 'tradedate';
    }

    /**
     * @return string
     */
    public function getStockIdField(): string
    {
        return 'moex_stock_id';
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
        return $this->tradedate;
    }
}
