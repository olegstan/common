<?php

namespace Common\Models\Catalog\Currency;

use Common\Models\Catalog\BaseStock;
use Common\Models\Currency;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogInterface;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;
use Common\Models\Traits\Catalog\CommonCatalogTrait;
use Carbon\Carbon;

/**
 * @property $cb_id
 * @property $num_code
 * @property $char_code
 * @property $nominal
 * @property $name
 */
class CbCurrency extends BaseStock implements CommonsFuncCatalogInterface
{
    //Связи с другими моделями
    use \Common\Models\Traits\Catalog\Currency\CurrencyRelationshipsTrait;

    //Возвращаемые данные для трансформеров, текущей сущности и тп
    use \Common\Models\Traits\Catalog\Currency\CurrencyScopeTrait;

    //функции запросов
    use \Common\Models\Traits\Catalog\Currency\CurrencyReturnGetDataFunc;

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
     * @param $classes
     * @return mixed
     */
    public function createBindActive($userId, $classes)
    {
        $active = $classes['cur']::create([
            'user_id' => $userId,
            'buy_currency_id' => Currency::getByCode(Currency::RUB)->id,
        ]);

        if ($active) {
            $this->active($active)->save($active);
        }

        return $active;
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
    public static function loadHistory($currency, Carbon $startDate, Carbon $endDate)
    {
        CbHistoryCurrencyCourse::loadHistory($currency, $startDate, $endDate);
    }
}
