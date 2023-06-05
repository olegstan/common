<?php

namespace App\src\Models\Catalog\MoscowExchange;

use App\src\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property $moex_stock_id
 * @property $issuevalue
 * @property $coupondate
 * @property $recorddate
 * @property $startdate
 * @property $value
 * @property $valueprc
 * @property $value_rub
 */
class MoscowExchangeCoupon extends BaseModel
{
    /**
     * @var string
     */
    public $table = 'moscow_exchange_coupons';

    /**
     * @var array
     */
    protected $fillable = [
        'moex_stock_id',
        'issuevalue',
        'coupondate',
        'recorddate',
        'startdate',
        'value',
        'valueprc',
        'value_rub',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'moex_stock_id' => 'integer',
        'issuevalue' => 'double',
        'coupondate' => 'date',
        'recorddate' => 'date',
        'startdate' => 'date',
        'value' => 'double',
        'valueprc' => 'double',
        'value_rub' => 'double',
    ];

    public $timestamps = false;

    /**
     * @return Carbon|null
     */
    public function getCouponDate(): ?Carbon
    {
        return $this->coupondate;
    }

    /**
     * @return float
     */
    public function getCouponValue(): float
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
