<?php

namespace Common\Models;
use Common\Models\Catalog\Currency\CbCurrency;
use Common\Models\Catalog\Currency\CbHistoryCurrencyCourse;
use Cache;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Currency
 *
 * @property $name
 * @property $code
 * @property $sign
 * @property $order
 * @property CbCurrency $cb_currency
 *
 * @package Models
 */
class Currency extends BaseModel
{
    const RUBBLE_ID = 1;
    const DOLLAR_ID = 11;
    const EURO_ID = 12;

    const RUB = 'RUB';
    const USD = 'USD';
    const EUR = 'EUR';
    const GBP = 'GBP';
    const HKD = 'HKD';
    const CHF = 'CHF';
    const JPY = 'JPY';
    const CNY = 'CNY';
    const TRL = 'TRY';

    /**
     * @var string
     */
    public $table = 'currencies';

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
        'name',
        'code',
        'sign',
        'order',
    ];

    /**
     * @param $sum
     * @param $currencyId
     * @param Carbon $date
     * @return float
     */
    public function convert($sum, $currencyId, Carbon $date)
    {
        try {
            /**
             * @var Currency $convertCurrency
             */
            $convertCurrency = Currency::getById($currencyId);

            //если один ID значит конвертация не требуется
            if ($this->id === $currencyId)
            {
                return $sum;
            }

            //если выбранная валюта рубль, тогда просто изпользуем
            //базу курсов с учётом даты
            if ($this->code === self::RUB)
            {
                $course = $this->getRubCourseByDate($convertCurrency, $date);

                return $sum * $course->value / $course->nominal;
            }

            $course = $this->getRubCourseByDate($this, $date);

            if ($convertCurrency->code === self::RUB)
            {
                return $sum * (1 / $course->value / $course->nominal);
            }

            $convertCourse = $this->getRubCourseByDate($convertCurrency, $date);

            return $sum * ($convertCourse->value / $convertCourse->nominal) / ($course->value / $course->nominal);
        }catch (Exception $e)
        {
            return $sum;
        }
    }

    /**
     * @param $currencyId
     * @param Carbon $date
     * @return float|int
     */
    public function getConvertCourse($currencyId, Carbon $date)
    {
        try {
            $convertCurrency = Currency::getById($currencyId);

            //если один ID значит конвертация не требуется
            if ($currencyId === $this->id) {
                return 1;
            }

            //если выбранная валюта рубль, тогда просто изпользуем
            //базу курсов с учётом даты
            if ($this->code === self::RUB)
            {
                //TODO переписать логику на один запрос, либо кешировать данные по курсам

                $course = $this->getRubCourseByDate($convertCurrency, $date);

                return floor($course->value / $course->nominal * 1000) / 1000;
            }

            $course = $this->getRubCourseByDate($this, $date);

            if ($convertCurrency->code === self::RUB)
            {
                return floor((1 / $course->value / $course->nominal) * 1000) / 1000;
            }

            $convertCourse = $this->getRubCourseByDate($convertCurrency, $date);

            return floor(($convertCourse->value / $convertCourse->nominal) / ($course->value / $course->nominal) * 1000) / 1000;
        }catch (Exception $e)
        {
            return 1;
        }
    }

    /**
     * @param $currency
     * @param Carbon $date
     * @return mixed
     */
    public function getRubCourseByDate($currency, Carbon $date)
    {
        $cacheString = 'cb_currency.' . $currency->cb_currency->id . ':date.' . $date->format('Y-m-d');

        return Cache::rememberForever($cacheString, static function () use ($currency, $date)
        {
            return CbHistoryCurrencyCourse::where('currency_id', $currency->cb_currency->id)
                ->where('date', '<', $date->format('Y-m-d'))
                ->orderBy('date', 'DESC')
                ->first();
        });
    }

    /**
     * @param $code
     * @return mixed
     * //TODO сделать проверку что только латинские буквы должна содержать строка, никаких цифр
     */
    public static function getByCode($code)
    {
        if($code === 'RUR')
        {
            $code = Currency::RUB;
        }

        return Cache::rememberForever('currency.' . $code, function () use ($code) {
            return self::where('code', $code)->first();
        });
    }

    /**
     * @param $currencyId
     * @return mixed
     */
    public static function getById($currencyId)
    {
        return Cache::rememberForever('currency.' . $currencyId, static function () use ($currencyId) {
            return Currency::where('id', $currencyId)
                ->with('cb_currency')
                ->first();
        });
    }

    /**
     * @return HasOne
     */
    public function cb_currency(): HasOne
    {
        return $this->hasOne(CbCurrency::class, 'char_code', 'code');
    }
}
