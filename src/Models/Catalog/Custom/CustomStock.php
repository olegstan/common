<?php

namespace Common\Models\Catalog\Custom;

use Common\Models\Catalog\BaseCatalog;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogInterface;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;
use Common\Models\Traits\Catalog\CommonCatalogTrait;

/**
 * @property $id
 * @property $name
 * @property $symbol
 * @property $type_id
 * @property $currency_id
 * @property $facevalue
 * @property $matdate
 * @property $rate_period_type_id
 * @property $rate
 */
class CustomStock extends BaseCatalog implements \Common\Models\Interfaces\Catalog\Custom\DefinitionCustomConst, CommonsFuncCatalogInterface
{
    //Связи с другими моделями
    use \Common\Models\Traits\Catalog\Custom\CustomRelationshipsTrait;

    //Возвращаемые данные для трансформеров, текущей сущности и тп
    use \Common\Models\Traits\Catalog\Custom\CustomScopeTrait;

    //функции запросов
    use \Common\Models\Traits\Catalog\Custom\CustomReturnGetDataFunc;

    //общие трейты
    use CommonCatalogTrait;

    /**
     * @var string
     */
    public $table = 'custom_stocks';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'symbol',
        'type_id',
        'currency_id',
        'facevalue',
        'matdate',
        'rate_period_type_id',
        'rate'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'symbol' => 'string',
        'type_id' => 'integer',
        'currency_id' => 'integer',
        'facevalue' => 'integer',
        'matdate' => 'datetime',
        'rate_period_type_id' => 'integer',
        'rate' => 'integer',
    ];


    /**
     * @param $data
     * @param $ticker
     * @param string $lang
     * @param int $limit
     * @param bool $cache
     * @return void
     */
    public static function search($data, $ticker, string $lang = 'ru', int $limit = 50, bool $cache = true)
    {
        //TODO:Тк теперь напрямую с парсерами не взаимодействуем, надо изменить логику
//        $name = null;
//        $currency = Currency::RUBBLE_ID;
//
//        if (!is_string($data)) {
//            /**
//             * @var AtonOperation|BcsOperation|TinkoffOperation $data
//             */
//            $name = $data->getName();
//            $currency = StockActive::getCurrency($data->getCurrency());
//        }
//
//        //тк негде искать, будет заглушкой
//        $stock = CustomStock::where('symbol', $ticker)
//            ->orWhere(function ($stock) use ($name) {
//                if (isset($name)) {
//                    $stock->where('name', $name);
//                }
//            })
//            ->first();
//
//        if (!$stock)
//        {
//            $stock = CustomStock::create([
//                'name' => $name ?? $ticker,
//                'symbol' => $ticker,
//                'type_id' => DefinitionActiveConst::STOCK,
//                'currency_id' => $currency,
//            ]);
//        }
//
//        return $stock;
    }

    /**
     * @param $original
     * @param $text
     * @param $translitText
     * @param $foundStocks
     * @param $items
     * @param $condition
     * @param bool $async
     * @return void
     */
    public static function createAndGet(
        $original,
        $text,
        $translitText,
        $foundStocks = null,
        &$items,
        $condition = null,
        bool $async = true
    ): void {
        $splitedWords = self::fullTextWildcards($text);

        $stocksQuery = self::selectRaw(
            '`custom_stocks`.*,MATCH (`custom_stocks`.`name`, `custom_stocks`.`symbol`) AGAINST (?) as relevance',
            [implode(' ', $splitedWords)]
        )
            ->search($original, $text, $translitText);

        if ($condition) {
            $condition($stocksQuery);
        }

        /**
         * @var CustomStock[] $stocks
         */
        $stocks = $stocksQuery->get();

        if ($stocks) {
            foreach ($stocks as $item) {
                /**
                 * @var CustomStock $item
                 */
                $items[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'type_id' => $item->getType(),
                    'symbol' => $item->getSymbol(),
                    'currency_id' => $item->getCurrency(),
                    'ticker' => 'catalog.4',
                    'type_text' => $item->getTypeText(),
                    'facevalue' => '',
                    'couponfrequency' => $item->getCouponFrequency(),
                    'coupondate' => '',
                    'couponpercent' => '',
                    'couponvalue' => '',
                    'decimals' => '',
                    'lotsize' => $item->getLotSize(),
                ];
            }
        }
    }

    /**
     * @param $userId
     * @param $currency_id
     * @param $accountId
     * @param $classes
     * @return mixed
     */
    public function createBindActive($userId, $currency_id, $accountId, $classes)
    {
        if(in_array($this->type_id, \Common\Models\Interfaces\Catalog\Custom\DefinitionCustomConst::BOND_VALUES))
        {
            return $classes['obligation']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::OBLIGATION_GROUP_TYPE,
                'buy_sum' => $this->facevalue,
                'buy_currency_id' => $currency_id,
                'buy_account_id' => $accountId,
                'sell_at' => null,
                'rate_period_type_id' => $this->getCouponFrequency(),
                'rate' => $this->rate,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if(in_array($this->type_id, \Common\Models\Interfaces\Catalog\Custom\DefinitionCustomConst::PIF_VALUES))
        {
            return $classes['pif']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::STOCK_GROUP_TYPE,
                'buy_currency_id' => $currency_id,
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if(in_array($this->type_id, \Common\Models\Interfaces\Catalog\Custom\DefinitionCustomConst::FUTURES_VALUE)){
            return $classes['futures']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::INSTRUMENT_CASH_FLOW_GROUP_TYPE,
                'buy_currency_id' => $currency_id,
                'buy_account_id' => $accountId,
                'sell_at' => null,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if(in_array($this->type_id, \Common\Models\Interfaces\Catalog\Custom\DefinitionCustomConst::ETF_VALUE)){
            return $classes['etf']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::STOCK_GROUP_TYPE,
                'buy_currency_id' => $currency_id,
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if(in_array($this->type_id, \Common\Models\Interfaces\Catalog\Custom\DefinitionCustomConst::CURRENCY_VALUE)){
            return $classes['currency']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::INSTRUMENT_CASH_FLOW_GROUP_TYPE,
                'buy_currency_id' => $currency_id,
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        return $classes['stock']::create([
            'user_id' => $userId,
            'group_type_id' => DefinitionActiveConst::STOCK_GROUP_TYPE,
            'buy_currency_id' => $currency_id,
            'buy_account_id' => $accountId,
            'item_type' => $this->getMorphClass(),
            'item_id' => $this->id,
        ]);
    }
}
