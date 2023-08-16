<?php

namespace Common\Models\Catalog\MoscowExchange;

use Common\Models\Catalog\BaseCatalog;
use Common\Models\Currency;
use Common\Models\Interfaces\Catalog\CouponInterface;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

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
class MoscowExchangeCoupon extends BaseCatalog implements CouponInterface
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
    public function getCouponValue(Currency $currency): ?float
    {
        //если валюта рубль, то просто берем из нужно поля в купоне
        //в таблице купонов однозначно определить валюту нельзя, поэтому можно брать из поля value_rub
        //далле считаем по курсу
        //для текущей и прошедших дат значение рассчитывается по курсу на соответствующую дату, для будущих выплат значение рассчитывается по курсу на текущую дату
        if($currency->id === Currency::RUBBLE_ID)
        {
            return $this->value_rub;
        }else{
            //если не рубль, то конвертим
            $value = $currency->convert(
                $this->value_rub,
                Currency::RUBBLE_ID,
                $this->getCouponDate()
            );
            $couponValue = $this->value;

            //если в итоге значение достаточно близкое к тому которое мы получили из справочника,
            //то нужно взять значение из справочника, так как оно точнее и без погрешности при конвертации

            //пример рассчёта
            // 26.7500000000
            // 1956.2400000000

            //рассчётно значение получилось 25,97
            if($couponValue - 2 <= $value && $value <= $couponValue + 2)
            {
                return $couponValue;
            }

            return $value;
        }
    }

    /**
     * @return HasOne
     */
    public function item(): HasOne
    {
        return $this->hasOne(MoscowExchangeStock::class, 'id', 'moex_stock_id');
    }
}
