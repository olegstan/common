<?php

namespace Common\Models\Catalog\Tinkoff;

use Common\Models\Catalog\BaseCatalog;

/**
 * Class TinkoffStock
 */
class TinkoffStock extends BaseCatalog
{
    /**
     * @var string
     */
    public $table = 'tinkoff_stocks';

    /**
     * @var array
     */
    protected $fillable = [
        'figi',
        'ticker',
        'isin',
        'minPriceIncrement',
        'lot',
        'currency',
        'name',
        'type',
    ];

    /**
     * @var array
     */
    protected $casts = [

    ];

    public $timestamps = false;
}
