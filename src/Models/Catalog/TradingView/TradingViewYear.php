<?php

namespace Common\Models\Catalog\TradingView;


use Common\Models\Catalog\BaseCatalog;
use Illuminate\Database\Eloquent\Relations\hasMany;
use Illuminate\Database\Eloquent\Relations\hasOne;

/**
 * @property $key_id
 * @property $year
 * @property $value
 * @property $percent
 * @property $ticker_id
 */
class TradingViewYear extends BaseCatalog
{
    /**
     * @var string
     */
    public $table = 'tv_years';

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
        'key_id',
        'year',
        'value',
        'percent',
        'ticker_id',
    ];

    protected $casts = [
        'key_id' => 'integer',
        'year' => 'integer',
        'value' => 'double',
        'percent' => 'integer',
        'ticker_id' => 'integer',
    ];

    /**
     * @return hasMany
     */
    public function ticker(): hasMany
    {
        return $this->hasMany(TradingViewTicker::class, 'id', 'ticker_id');
    }

    /**
     * @return hasOne
     */
    public function key(): hasOne
    {
        return $this->hasOne(TradingViewKey::class, 'id', 'key_id');
    }
}
