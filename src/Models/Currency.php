<?php

namespace Common\Models;

use Cache;
use Carbon\Carbon;
use Common\Helpers\LoggerHelper;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Catalog\Currency\CbCurrency;
use Common\Models\Catalog\Currency\CbHistoryCurrencyCourse;
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
class Currency extends BaseCatalog
{
    const RUB_ID = 1;
    const USD_ID = 12;
    const EUR_ID = 13;

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
            LoggerHelper::getLogger('convert')->error($e);
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
        //нельзя кешировать навсегда, так как мы пробуем получить курс из будущего, а в итоге
        //возвращаем самый последний, если кэш будет бессрочным, то курс устареет и будет показывать
        //неправильные данные
        $cacheString = 'cb_currency.' . $currency->cb_currency->id . ':date.' . $date->format('Y-m-d');

        return Cache::tags(['catalog'])->remember($cacheString, Carbon::now()->addDay(), static function () use ($currency, $date)
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

        if(!$code)
        {
            return null;
        }

        return Cache::tags(['catalog'])->rememberForever('currency.' . $code, function () use ($code)
        {
            $cuurency = Currency::where('code', $code)
                ->first();

            if(!$cuurency)
            {
                throw new Exception('Currency not found by code ' . $code);
            }

            return $cuurency;
        });
    }

    /**
     * @param $currencyId
     * @return mixed
     */
    public static function getById($currencyId)
    {
        if(!$currencyId)
        {
            return null;
        }

        return Cache::tags(['catalog'])->rememberForever('currency.' . $currencyId, static function () use ($currencyId) {
            $cuurency = Currency::where('id', $currencyId)
                ->with('cb_currency')
                ->first();

            if(!$cuurency)
            {
                throw new Exception('Currency not found by id ' . $cuurency);
            }

            return $cuurency;
        });
    }

    /**
     * @param $currencyId
     * @return mixed
     */
    public static function getCodeById($currencyId)
    {
        return Cache::tags(['catalog'])->rememberForever('currency.code.' . $currencyId, static function () use ($currencyId) {
            $curr = Currency::where('id', $currencyId)
                ->first();

            if($curr)
            {
                return $curr->code;
            }
        });
    }

    /**
     * @param $currencyId
     * @return mixed
     */
    public static function getSignById($currencyId)
    {
        return Cache::tags(['catalog'])->rememberForever('currency.sign.' . $currencyId, static function () use ($currencyId) {
            $curr = Currency::where('id', $currencyId)
                ->first();

            if($curr)
            {
                return $curr->sign;
            }
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
