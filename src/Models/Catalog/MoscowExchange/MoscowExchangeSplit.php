<?php

namespace Common\Models\Catalog\MoscowExchange;

use Common\Models\Catalog\BaseCatalog;

/**
 * Class MoscowExchangeSplit
 * @package Common\Models\Catalog\MoscowExchange
 */
class MoscowExchangeSplit extends BaseCatalog
{
    /**
     * @var string
     */
    protected $table = 'moscow_exchange_splits';

    /**
     * @var string[]
     */
    protected $fillable = [
        'moex_stock_id',
        'before',
        'after',
        'date',
    ];

    protected $casts = [
        'before' => 'double',
        'after' => 'double',
        'date' => 'datetime',
    ];
}
