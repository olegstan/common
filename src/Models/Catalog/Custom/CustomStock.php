<?php

namespace Common\Models\Catalog\Custom;

use Carbon\Carbon;
use Common\Helpers\LoggerHelper;
use Common\Models\Catalog\BaseCatalog;
use Cache;
use Common\Models\Catalog\Currency\CbCurrency;
use Common\Models\Currency;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogInterface;
use Common\Models\Interfaces\Catalog\Custom\DefinitionCustomConst;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;
use Common\Models\Traits\Catalog\CommonCatalogTrait;
use Common\Models\Traits\Catalog\Custom\CustomRelationshipsTrait;
use Common\Models\Traits\Catalog\Custom\CustomReturnGetDataFunc;
use Common\Models\Traits\Catalog\Custom\CustomScopeTrait;
use Exception;

/**
 * @property $id
 * @property $name
 * @property $symbol
 * @property $type_id
 * @property $user_id
 * @property $currency_id
 * @property $facevalue
 * @property $matdate
 * @property $rate_period_type_id
 * @property $rate
 */
class CustomStock extends BaseCatalog implements DefinitionCustomConst, CommonsFuncCatalogInterface
{
    //Связи с другими моделями
    use CustomRelationshipsTrait;

    //Возвращаемые данные для трансформеров, текущей сущности и тп
    use CustomScopeTrait;

    //функции запросов
    use CustomReturnGetDataFunc;

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
        'user_id',
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
        'user_id' => 'string',
        'currency_id' => 'integer',
        'facevalue' => 'integer',
        'matdate' => 'datetime',
        'rate_period_type_id' => 'integer',
        'rate' => 'integer',
    ];


    /**
     * @param $data
     * @param $cache
     * @return void
     */
    public static function search($data, $cache = true)
    {
        //в $data всегда должен быть объект модели, что бы мы могли искать не по 1 параметру, а сколько требуется
        if ($data->getTicker()) {
            $searchText = $data->getTicker();
        }

        try {
            if ($cache && Cache::has('custom' . $searchText)) {
                return Cache::get('custom' . $searchText);
            }

            $userId = config('app.env') . '-' . $data->user_id;
            $currency = Currency::firstWhere('code', $data->getCurrency());

            $custom = CustomStock::where('symbol', $searchText ?? null)
                ->where('name', $data->getName())
                ->where('user_id', $userId)
                ->where('currency_id', $currency)
                ->where('type_id', $data->getCustomStockType())
                ->first();

            if (!$custom) {
                $custom = CustomStock::create([
                    'symbol' => $searchText ?? null,
                    'name' => $data->getName(),
                    'user_id' => $userId,
                    'currency_id' => $currency,
                    'type_id' => $data->getCustomStockType()
                ]);
            }

            Cache::put('custom' . $searchText, $custom, Carbon::now()->addDay());

            return $custom;
        } catch (Exception $e) {
            LoggerHelper::getLogger('custom')->error($e);
            return false;
        }
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
            '`custom_stocks`.*,MATCH (`custom_stocks`.`name`, `custom_stocks`.`user_id`) AGAINST (?) as relevance',
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
                    'user_id' => $item->user_id,
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
        if (in_array($this->type_id, DefinitionCustomConst::BOND_VALUES)) {
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

        if (in_array($this->type_id, DefinitionCustomConst::PIF_VALUES)) {
            return $classes['pif']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::STOCK_GROUP_TYPE,
                'buy_currency_id' => $currency_id,
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if (in_array($this->type_id, DefinitionCustomConst::FUTURES_VALUE)) {
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

        if (in_array($this->type_id, DefinitionCustomConst::ETF_VALUE)) {
            return $classes['etf']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::STOCK_GROUP_TYPE,
                'buy_currency_id' => $currency_id,
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if (in_array($this->type_id, DefinitionCustomConst::CURRENCY_VALUE)) {
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
