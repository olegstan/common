<?php

namespace Common\Models\Catalog\Cbond;

use Carbon\Carbon;
use Common\Helpers\CatalogCache;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Currency;
use Common\Models\Interfaces\Catalog\CouponInterface;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    public function getValue(): ?float
    {
        $item = CatalogCache::getCbondItem($this->cbond_stock_id);

        $percent = $this->valueprc;
        $couponfrequency = $item->couponfrequency;

        if ($couponfrequency > 0) {
            return $item->facevalue * $percent / $couponfrequency / 100;
        }

        return 0;
    }

    /**
     * @param Currency $currency
     *
     * @return float|null
     */
    public function getCouponValue(Currency $currency): ?float
    {
        $bondCodes = json_decode($this->item->faceunit);
        $percent = $this->valueprc;
        $couponFrequency = $this->item->couponfrequency;

        if ($couponFrequency <= 0) {
            return 0.0;
        }

        $faceValue = $this->item->facevalue;
        $couponValue = $faceValue * $percent / $couponFrequency / 100;

        if (empty($bondCodes) || !isset($bondCodes[0])) {
            return 0.0;
        }

        $couponCurrency = Currency::getByCode($bondCodes[0]);

        if (!$couponCurrency) {
            return 0.0;
        }

        if ($currency->id === $couponCurrency->id) {
            return $couponValue;
        }

        // Если валюты отличаются, конвертируем
        return $currency->convert(
            $couponValue,
            $couponCurrency->id,
            $this->getCouponDate(),
        );
    }

    /**
     * @return HasOne
     */
    public function item(): HasOne
    {
        return $this->hasOne(CbondStock::class, 'id', 'cbond_stock_id');
    }
}