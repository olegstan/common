<?php

namespace Common\Models\Catalog\Cbond;

use Carbon\Carbon;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Currency as Cur;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogHistoryInterface;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Cache;

/**
 * @property $cbond_stock_id
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
class CbondHistory extends BaseCatalog implements CommonsFuncCatalogHistoryInterface
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
        return $this->hasOne(CbondStock::class, 'id', 'cbond_stock_id');
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

    /**
     * @param $priceKey
     * @param $dateKey
     * @return array
     */
    public function setPrice($priceKey, $dateKey)
    {
        $cbondCurrencyCode = $this->faceunit;

        if ($cbondCurrencyCode === Cur::RUB) {
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
                Cur::getByCode($cbondCurrencyCode)->id,
                Carbon::now()
            );

            Cache::forever($priceKey, $convertedPrice);
            Cache::forever($dateKey, $date && $date instanceof Carbon ? $date->format('Y-m-d') : null);
            return [$priceKey, $convertedPrice, $date];
        }
    }
}
