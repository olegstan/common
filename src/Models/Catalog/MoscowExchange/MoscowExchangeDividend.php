<?php

namespace Common\Models\Catalog\MoscowExchange;

use Common\Models\BaseModel;
use Common\Models\Catalog\BaseCatalog;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property $moex_stock_id
 * @property $registryclosedate
 * @property $value
 * @property $currencyid
 */
class MoscowExchangeDividend extends BaseCatalog
{
    /**
     * @var string
     */
    public $table = 'moscow_exchange_dividends';

    /**
     * @var array
     */
    protected $fillable = [
        'moex_stock_id',
        'registryclosedate',
        'value',
        'currencyid',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'moex_stock_id' => 'integer',
        'registryclosedate' => 'date',
        'value' => 'double',
        'currencyid' => 'string',
    ];

    public $timestamps = false;

    /**
     * @return Carbon|null
     */
    public function getDividendDate(): ?Carbon
    {
        return $this->registryclosedate;
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
        return $this->hasOne(MoscowExchangeStock::class, 'moex_stock_id');
    }

}
