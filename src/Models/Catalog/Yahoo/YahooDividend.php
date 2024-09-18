<?php

namespace Common\Models\Catalog\Yahoo;

use Carbon\Carbon;
use Common\Models\Catalog\BaseCatalog;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property $moex_stock_id
 * @property $registryclosedate
 * @property $value
 * @property $currencyid
 */
class YahooDividend extends BaseCatalog
{
    /**
     * @var string
     */
    public $table = 'yahoo_dividends';

    /**
     * @var array
     */
    protected $fillable = [
        'yahoo_stock_id',
        'date',
        'value',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'yahoo_stock_id' => 'integer',
        'date' => 'date',
        'value' => 'double',
    ];

    /**
     * @return Carbon
     */
    public function getDividendDate(): Carbon
    {
        return $this->date;
    }

    /**
     * @return float
     */
    public function getDividendValue(): float
    {
        return $this->value;
    }

    /**
     * @return HasOne
     */
    public function item(): HasOne
    {
        return $this->hasOne(YahooStock::class, 'id', 'yahoo_stock_id');
    }
}
