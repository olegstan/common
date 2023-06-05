<?php

namespace App\src\Models\Catalog\TradingView;

use App\src\Models\BaseModel;

/**
 * @property int $id
 * @property string $key
 * @property string $en
 * @property string $ru
 */
class TradingViewKey extends BaseModel
{
    /**
     * @var string
     */
    public $table = 'tv_keys';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'en',
        'ru',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'key' => 'string',
        'en' => 'string',
        'ru' => 'string',
    ];
}
