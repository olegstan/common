<?php

namespace Common\Models\Catalog\Tinkoff;

use Common\Models\Catalog\BaseCatalog;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property $dividend_net
 * @property $payment_date
 * @property $declared_date
 * @property $last_buy_date
 * @property $dividend_type
 * @property $record_date
 * @property $regularity
 * @property $close_price
 * @property $yield_value
 * @property $created_at
 * @property $tinkoff_stock_id
 */
class TinkoffDividend extends BaseCatalog
{
    /**
     * @var string
     */
    public $table = 'tinkoff_dividends';

    /**
     * @var array
     */
    protected $fillable = [
        'dividend_net',
        'payment_date',
        'declared_date',
        'last_buy_date',
        'dividend_type',
        'record_date',
        'regularity',
        'close_price',
        'yield_value',
        'created_at',
        'tinkoff_stock_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'dividend_net' => 'double',
        'payment_date' => 'datetime',
        'declared_date' => 'datetime',
        'last_buy_date' => 'datetime',
        'dividend_type' => 'string',
        'record_date' => 'datetime',
        'regularity' => 'string',
        'close_price' => 'double',
        'yield_value' => 'double',
        'created_at' => 'datetime',
        'tinkoff_stock_id' => 'integer',
    ];

    public $timestamps = false;

    /**
     * @return HasOne
     */
    public function stock(): HasOne
    {
        return $this->hasOne(TinkoffStock::class, 'id', 'tinkoff_stock_id');
    }
}
