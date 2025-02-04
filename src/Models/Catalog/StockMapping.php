<?php

namespace Common\Models\Catalog;

use Common\Models\Catalog\Cbond\CbondStock;
use Common\Models\Catalog\Custom\CustomStock;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeStock;
use Common\Models\Catalog\Tinkoff\TinkoffStock;
use Common\Models\Catalog\TradingView\TradingViewTicker;
use Common\Models\Catalog\Yahoo\YahooStock;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $ticker
 * @property integer $moscow_exchange_stocks_id
 * @property integer $cbond_stocks_id
 * @property integer $custom_stocks_id
 * @property integer $tinkoff_stocks_id
 * @property integer $yahoo_stocks_id
 * @property integer $tv_tickers_id
 */
class StockMapping extends BaseCatalog
{
    use HasFactory;

    /**
     * @var string
     */
    public $table = 'stock_mappings';

    /**
     * @var array
     */
    protected $fillable = [
        'ticker',
        'moscow_exchange_stocks_id',
        'cbond_stocks_id',
        'custom_stocks_id',
        'tinkoff_stocks_id',
        'yahoo_stocks_id',
        'tv_tickers_id',
    ];
    /**
     * @var array
     */
    protected $casts = [
        'ticker' => 'string',
        'moscow_exchange_stocks_id' => 'integer',
        'cbond_stocks_id' => 'integer',
        'custom_stocks_id' => 'integer',
        'tinkoff_stocks_id' => 'integer',
        'yahoo_stocks_id' => 'integer',
        'tv_tickers_id' => 'integer',
    ];

    public function moscowExchangeStock()
    {
        return $this->belongsTo(MoscowExchangeStock::class, 'moscow_exchange_stocks_id');
    }

    public function cbondStock()
    {
        return $this->belongsTo(CbondStock::class, 'cbond_stocks_id');
    }

    public function customStock()
    {
        return $this->belongsTo(CustomStock::class, 'custom_stocks_id');
    }

    public function tinkoffStock()
    {
        return $this->belongsTo(TinkoffStock::class, 'tinkoff_stocks_id');
    }

    public function yahooStock()
    {
        return $this->belongsTo(YahooStock::class, 'yahoo_stocks_id');
    }

    public function tvTicker()
    {
        return $this->belongsTo(TradingViewTicker::class, 'tv_tickers_id');
    }
}