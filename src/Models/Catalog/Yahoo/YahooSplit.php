<?php

namespace Common\Models\Catalog\Yahoo;

use Common\Models\Catalog\BaseCatalog;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property $yahoo_stock_id
 * @property $before
 * @property $after
 * @property $date
 */
class YahooSplit extends BaseCatalog
{
    /**
     * @var string
     */
    protected $table = 'yahoo_splits';

    /**
     * @var string[]
     */
    protected $fillable = [
        'yahoo_stock_id',
        'before',
        'after',
        'date',
    ];

    protected $casts = [
        'yahoo_stock_id' => 'integer',
        'before' => 'double',
        'after' => 'double',
        'date' => 'date',
    ];

    /**
     * @return HasOne
     */
    public function item(): HasOne
    {
        return $this->hasOne(YahooStock::class, 'id', 'yahoo_stock_id');
    }
}
