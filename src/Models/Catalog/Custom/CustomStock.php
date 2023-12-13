<?php

namespace Common\Models\Catalog\Custom;

use App\Models\Actives\ActiveTrade;
use App\Models\Aton\AtonOperation;
use Cache;
use Carbon\Carbon;
use Common\Helpers\LoggerHelper;
use Common\Models\Catalog\BaseCatalog;
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
        'currency_id' => 'string',
        'facevalue' => 'integer',
        'matdate' => 'datetime',
        'rate_period_type_id' => 'integer',
        'rate' => 'integer',
    ];


    /**
     * @param AtonOperation $data
     * @param bool $cache
     * @return false|void
     */
    public static function search($data, bool $cache = true)
    {
        //в $data всегда должен быть объект модели, что бы мы могли искать не по 1 параметру, а сколько требуется
        if (!$data->getTicker()) {
            return false;
        }

        $userId = config('app.env') . '-' . $data->user_id;
        $searchText = $data->getTicker();

        try {
            if (isset($searchText) && $cache && Cache::has('custom-' . $userId . '-' . $searchText)) {
                return Cache::get('custom-' . $userId . '-' . $searchText);
            }

            $custom = CustomStock::where('symbol', $searchText ?? null)
                ->where('name', $data->getName())
                ->where('user_id', $userId)
                ->where('currency_id', $data->getCurrency())
                ->where('type_id', $data->getCustomStockType())
                ->first();

            if (!$custom) {
                $custom = CustomStock::create([
                    'symbol' => $searchText ?? null,
                    'name' => $data->getName(),
                    'user_id' => $userId,
                    'currency_id' => $data->getCurrency(),
                    'type_id' => $data->getCustomStockType()
                ]);
            }

            Cache::put('custom-' . $userId . '-' . $searchText, $custom, Carbon::now()->addDay());

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
                    'name' => $item->name . ' ' . $item->symbol,
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
    public function createBindActive($userId, $currencyId, $accountId, $classes)
    {
        if (in_array($this->type_id, DefinitionCustomConst::BOND_VALUES)) {
            return $classes['obligation']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::OBLIGATION_GROUP_TYPE,
                'buy_sum' => $this->facevalue,
                'buy_currency_id' => $currencyId,
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
                'buy_currency_id' => $currencyId,
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if (in_array($this->type_id, DefinitionCustomConst::FUTURES_VALUE)) {
            return $classes['futures']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::INSTRUMENT_CASH_FLOW_GROUP_TYPE,
                'buy_currency_id' => $currencyId,
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
                'buy_currency_id' => $currencyId,
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if (in_array($this->type_id, DefinitionCustomConst::CURRENCY_VALUE)) {
            return $classes['currency']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::INSTRUMENT_CASH_FLOW_GROUP_TYPE,
                'buy_currency_id' => $currencyId,
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        return $classes['stock']::create([
            'user_id' => $userId,
            'group_type_id' => DefinitionActiveConst::STOCK_GROUP_TYPE,
            'buy_currency_id' => $currencyId,
            'buy_account_id' => $accountId,
            'item_type' => $this->getMorphClass(),
            'item_id' => $this->id,
        ]);
    }

    /**
     * @param Currency $currency
     * @param null $date
     * @return \Illuminate\Database\Eloquent\HigherOrderBuilderProxy|int|mixed
     */
    public function getLastPriceByDate($currency, $date = null)
    {
        //TODO используется класс из основного проекта, лучше бы как то переделать и вынести логику из метода

        //ищем все ID кастомных активов где может быть такой же каталог
        $ids = CustomStock::where('symbol', $this->symbol)
            ->pluck('id')
            ->toArray();

        $trade = ActiveTrade::whereHas('active', function ($query) use ($ids)
        {
            $query->where('item_type', $this->getMorphClass())
                ->whereIn('item_id', $ids);
        })
            ->where('price', '>', 0)
            ->orderByDesc('trade_at')
            ->first();

        if($trade)
        {
            if($currency->id !== $trade->currency_id)
            {
                return $currency->convert($trade->price, $trade->currency_id, $trade->trade_at);
            }


            return $trade->price;
        }

        return 0;
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
            ->pluck('price', $this->getDateField());

        if ($history) {
            return $history;
        }

        return false;
    }

    /**
     * @param $stock
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return bool
     * polymorhic method
     */
    public static function loadHistory($stock, Carbon $startDate, Carbon $endDate)
    {
        //тк заранее все спаршено, будет заглушкой
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
