<?php

namespace App\src\Models\Catalog\TradingView;


use App\src\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\hasMany;
use Illuminate\Database\Eloquent\Relations\hasOne;

/**
 * @property $key_id
 * @property $quarter
 * @property $year
 * @property $value
 * @property $percent
 * @property $ticker_id
 */
class TradingViewQuarter extends BaseModel
{
    /**
     * @var string
     */
    public $table = 'tv_quarterlys';

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
        'quarter',
        'year',
        'value',
        'percent',
        'ticker_id',
    ];

    protected $casts = [
        'key_id' => 'integer',
        'quarter' => 'integer',
        'year' => 'string',
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
