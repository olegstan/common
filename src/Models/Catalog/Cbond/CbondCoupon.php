<?php

namespace Common\Models\Catalog\Cbond;

use Common\Models\BaseModel;
use Common\Models\Catalog\BaseCatalog;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property $cbond_stock_id
 * @property $issuevalue
 * @property $coupondate
 * @property $recorddate
 * @property $startdate
 * @property $value
 * @property $valueprc
 * @property $value_rub
 */
class CbondCoupon extends BaseCatalog
{
    /**
     * @var string
     */
    public $table = 'cbond_coupons';

    /**
     * @var array
     */
    protected $fillable = [
        'cbond_stock_id',
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
        'cbond_stock_id' => 'integer',
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
     * @return float|null
     */
    public function getCouponValue(): ?float
    {
        return $this->value;
    }

    /**
     * @return HasOne
     */
    public function item(): HasOne
    {
        return $this->hasOne(CbondStock::class, 'cbond_stock_id');
    }
}
