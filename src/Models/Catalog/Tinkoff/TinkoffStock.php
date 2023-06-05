<?php

namespace App\src\Models\Catalog\Tinkoff;

use App\src\Models\Catalog\BaseStock;

/**
 * Class TinkoffStock
 */
class TinkoffStock extends BaseStock
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
