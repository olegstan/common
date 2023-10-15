<?php

namespace Common\Models\Catalog\Cbond;

use Common\Models\Catalog\BaseCatalog;
use Common\Models\Currency;
use Common\Models\Interfaces\Catalog\CouponInterface;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

/**
 * @property $cbond_stock_id
 * @property $issuevalue
 * @property $coupondate
 * @property $recorddate
 * @property $startdate
 * @property $value
 * @property $valueprc
 * @property $value_rub
 * @property CbondStock $item
 */
class CbondCoupon extends BaseCatalog implements CouponInterface
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
    public function getCouponValue(Currency $currency): ?float
    {
        $bondCode = json_decode($this->item->faceunit);

        $percent = $this->valueprc;
        $couponfrequency = $this->item->couponfrequency;
        $value = $this->item->facevalue * $percent / $couponfrequency / 100;

        if(isset($bondCode[0]))
        {
            $couponCurrency = Currency::getByCode($bondCode[0]);

            if($currency->id === $couponCurrency->id)
            {
                return $value;
            }else{
                //если валюты отличаются то конвертим
                return $currency->convert(
                    $value,
                    $couponCurrency->id,
                    $this->getCouponDate()
                );
            }
        }

        return 0;
    }

    /**
     * @return HasOne
     */
    public function item(): HasOne
    {
        return $this->hasOne(CbondStock::class, 'id', 'cbond_stock_id');
    }
}
