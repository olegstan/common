<?php

namespace Common\Models\Catalog\MoscowExchange;

use Common\Models\Catalog\BaseCatalog;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class MoscowExchangeSplit
 *
 * @property $moex_stock_id
 * @property $before
 * @property $after
 * @property $date
 *
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
        'date' => 'date',
    ];

    /**
     * @return HasOne
     */
    public function item(): HasOne
    {
        return $this->hasOne(MoscowExchangeStock::class, 'id', 'moex_stock_id');
    }
}
