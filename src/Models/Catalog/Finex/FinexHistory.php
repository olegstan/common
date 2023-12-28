<?php

namespace Common\Models\Catalog\Finex;

use Cache;
use Carbon\Carbon;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeStock;
use Common\Models\Currency as Cur;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogHistoryInterface;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property $moex_stock_id
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
 */
class FinexHistory extends BaseCatalog implements CommonsFuncCatalogHistoryInterface
{
    /**
     * @var string
     */
    public $table = 'finex_history';

    /**
     * @var array
     */
    protected $fillable = [
        'moex_stock_id',
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
        'moex_stock_id' => 'integer',
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
        return $this->hasOne(MoscowExchangeStock::class, 'id', 'moex_stock_id');
    }

    /**
     * @param $query
     * @param MoscowExchangeStock $item
     * @return void
     */
    public function scopeByItem($query, MoscowExchangeStock $item)
    {
        $query->where('isin', $item->isin);
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
            Cache::tags(['back'])->forever($priceKey, $price);
            Cache::tags(['back'])->forever($dateKey, $date && $date instanceof Carbon ? $date->format('Y-m-d') : null);
            return [$priceKey, $price, $date, null, 'finex'];
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

            Cache::tags(['catalog'])->forever($priceKey, $convertedPrice);
            Cache::tags(['catalog'])->forever($dateKey, $date && $date instanceof Carbon ? $date->format('Y-m-d') : null);
            return [$priceKey, $convertedPrice, $date, $price, 'finex'];
        }
    }
}
