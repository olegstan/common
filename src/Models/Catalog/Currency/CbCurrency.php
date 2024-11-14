<?php

namespace Common\Models\Catalog\Currency;

use Cache;
use Carbon\Carbon;
use Common\Helpers\LoggerHelper;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Currency;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogInterface;
use Common\Models\Traits\Catalog\CommonCatalogTrait;
use Common\Models\Traits\Catalog\Currency\CurrencyRelationshipsTrait;
use Common\Models\Traits\Catalog\Currency\CurrencyReturnGetDataFunc;
use Common\Models\Traits\Catalog\Currency\CurrencyScopeTrait;
use Common\Models\Traits\Catalog\SearchActiveCatalogTrait;
use Exception;

/**
 * @property $cb_id
 * @property $num_code
 * @property $char_code
 * @property $nominal
 * @property $name
 */
class CbCurrency extends BaseCatalog implements CommonsFuncCatalogInterface
{
    //Связи с другими моделями
    use CurrencyRelationshipsTrait;

    //Возвращаемые данные для трансформеров, текущей сущности и тп
    use CurrencyScopeTrait;

    //функции запросов
    use CurrencyReturnGetDataFunc;

    //общие трейты
    use CommonCatalogTrait;

    //Общий трейт для каталогов и Актива для поиска бумаг
    use SearchActiveCatalogTrait;

    /**
     * @var string
     */
    public $table = 'cb_currencies';

    /**
     * @var array
     */
    protected $fillable = [
        'cb_id',
        'num_code',
        'char_code',
        'nominal',
        'name',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'cb_id' => 'string',
        'num_code' => 'string',
        'char_code' => 'string',
        'nominal' => 'string',
        'name' => 'string',
    ];

    public $timestamps = false;

    /**
     * @param $userId
     * @param $currencyId
     * @param $accountId
     * @param $classes
     * @return mixed
     */
    public function createBindActive($userId, $currencyId, $accountId, $classes)
    {
        return $classes['cur']::create([
            'user_id' => $userId,
            'buy_currency_id' => Currency::getByCode(Currency::RUB)->id,
            'item_id' => $this->id,
            'item_type' => $this->getMorphClass(),
        ]);
    }

    /**
     * @param Currency $currency
     * @param Carbon|null $date
     *
     * @return float|int
     */
    public function getLastPriceByDate(Currency $currency, Carbon $date = null)
    {
        $history = $this->history()
            ->when($date, function ($query) use ($date){
                $query->whereDate($this->getDateField(), '<=', $date);
            })
            ->where('value', '>', 0)
            ->orderByDesc($this->getDateField())
            ->first();

        if ($history) {
            $historyCurrency = Currency::getByCode($this->char_code);
            if ($historyCurrency) {
                return $currency->convert($history->getValue(), $historyCurrency->id, $date);
            }

            return $history->getValue();
        }

        return 0;
    }

    /**
     * @param $original
     * @param $text
     * @param $translitText
     * @param $items
     * @return void
     */
    public static function createAndGet($original, $text, $translitText, &$items)
    {
        $splitedWords = self::fullTextWildcards($text);
        $currencies = self::selectRaw(
            '*, MATCH (char_code, name) AGAINST (?) as relevance',
            [implode(' ', $splitedWords)]
        )
            ->search($original, $text, $translitText)
            ->orderByRaw("relevance DESC")
            ->limit(10)
            ->get();

        /**
         * @var CbCurrency $item
         */
        foreach ($currencies as $item) {
            $items[] = $item->getItemData();
        }
    }

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return false
     */
    public function getPriceHistory(Carbon $startDate, Carbon $endDate)
    {
        $history = $this->history()
            ->whereBetween($this->getDateField(), [$startDate, $endDate])
            ->pluck('value', $this->getDateField());

        if ($history) {
            return $history;
        }

        return false;
    }

    /**
     * @param $currency
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return void
     */
    public static function loadHistory($stock, Carbon $startDate, Carbon $endDate, $forceSkipCache = false)
    {
        CbHistoryCurrencyCourse::loadHistory($stock, $startDate, $endDate);
    }

    /**
     * @param $stock
     * @return void
     * polymorhic method
     */
    public static function loadCoupons($stock): void
    {

    }

    /**
     * @param $stock
     * @return void
     * polymorhic method
     */
    public static function loadDividends($stock): void
    {

    }

    /**
     * @param $code
     *
     * @return mixed
     * //TODO сделать проверку что только латинские буквы должна содержать строка, никаких цифр
     */
    public static function getByCharCode($code)
    {
        if ($code === 'RUR') {
            $code = Currency::RUB;
        }

        if (!$code) {
            return null;
        }

        try {
            return Cache::tags([config('cache.tags')])->rememberForever('cbcurrency.' . $code, function () use ($code) {
                $curency = CbCurrency::where('char_code', $code)
                    ->first();

                if (!$curency) {
                    LoggerHelper::getLogger()->error('Currency not found by code ' . $code);
                    return null;
                }

                return $curency;
            });
        } catch (Exception $e) {
            LoggerHelper::getLogger()->error($e);
            return null;
        }
    }

    /**
     * @param $currencyId
     *
     * @return mixed
     */
    public static function getById($currencyId)
    {
        if (!$currencyId) {
            return null;
        }

        try {
            return Cache::tags([config('cache.tags')])->rememberForever(
                'cbcurrency.' . $currencyId,
                static function () use ($currencyId) {
                    $curency = CbCurrency::where('id', $currencyId)
                        ->first();

                    if (!$curency) {
                        throw new Exception('Cb currency not found by id ' . $currencyId);
                    }

                    return $curency;
                },
            );
        } catch (Exception $e) {
            LoggerHelper::getLogger()->error($e);
            return null;
        }
    }
}
