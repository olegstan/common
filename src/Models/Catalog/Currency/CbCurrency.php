<?php

namespace Common\Models\Catalog\Currency;

use Carbon\Carbon;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Currency;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogInterface;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;
use Common\Models\Traits\Catalog\CommonCatalogTrait;
use Common\Models\Traits\Catalog\Currency\CurrencyRelationshipsTrait;
use Common\Models\Traits\Catalog\Currency\CurrencyReturnGetDataFunc;
use Common\Models\Traits\Catalog\Currency\CurrencyScopeTrait;

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
    
    public function getLastPriceByDate($date = null)
    {

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
            $items[] = [
                'id' => $item->id,
                'name' => $item->char_code . ' - ' . $item->name,
                'type_id' => DefinitionActiveConst::CURRENCY,
                'type_text' => 'Валюта',
                'currency_id' => '',
                'order' => '',
                'ticker' => 'catalog.1',
                'symbol' => $item->getSymbol(),
                'country' => '',
                'industry' => '',
                'sector' => '',
                'capitalization' => '',
            ];
        }
    }

    /**
     * @param $currency
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return void
     */
    public static function loadHistory($stock, Carbon $startDate, Carbon $endDate)
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
}
