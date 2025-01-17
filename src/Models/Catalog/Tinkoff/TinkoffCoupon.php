<?php

namespace Common\Models\Catalog\Tinkoff;

use Common\Models\Catalog\BaseCatalog;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property $figi
 * @property $coupon_date
 * @property $coupon_number
 * @property $fix_date
 * @property $pay_one_bond
 * @property $coupon_type
 * @property $coupon_start_date
 * @property $coupon_end_date
 * @property $coupon_period
 * @property $tinkoff_stock_id
 */
class TinkoffCoupon extends BaseCatalog
{
    /**
     * @var string
     */
    public $table = 'tinkoff_coupons';

    /**
     * @var array
     */
    protected $fillable = [
        'figi',
        'coupon_date',
        'coupon_number',
        'fix_date',
        'pay_one_bond',
        'coupon_type',
        'coupon_start_date',
        'coupon_end_date',
        'coupon_period',
        'tinkoff_stock_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'figi' => 'string',
        'coupon_date' => 'datetime',
        'coupon_number' => 'integer',
        'fix_date' => 'datetime',
        'pay_one_bond' => 'double',
        'coupon_type' => 'integer',
        'coupon_start_date' => 'datetime',
        'coupon_end_date' => 'datetime',
        'coupon_period' => 'integer',
        'tinkoff_stock_id' => 'integer',
    ];

    public $timestamps = false;

    /**
     * @return HasOne
     */
    public function stock(): HasOne
    {
        return $this->hasOne(TinkoffStock::class, 'id', 'tinkoff_stock_id');
    }
}
