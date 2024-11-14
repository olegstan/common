<?php

namespace Common\Models\Catalog\Custom;

use App\Models\Accounts\UserSubaccount;
use App\Models\Actives\ActiveTrade;
use App\Models\Aton\AtonOperation;
use Cache;
use Carbon\Carbon;
use Common\Helpers\Catalog\CatalogSearch;
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
use Common\Models\Traits\Catalog\SearchActiveCatalogTrait;
use Common\Models\Users\User;
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

    //Общий трейт для каталогов и Актива для поиска бумаг
    use SearchActiveCatalogTrait;

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
        'rate',
        'lotsize',
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
        'lotsize' => 'integer',
    ];

    /**
     * @param array $attributes
     *
     * @return static
     */
    public static function create(array $attributes = []): CustomStock
    {
        $model = new static($attributes);
        $model->save();

        if (CatalogSearch::isElasticsearchHealthy()) {
            CatalogSearch::indexRecordInElasticsearch($model, 'custom_stocks');
        }
        return $model;
    }

    /**
     * Поиск или создание кастомной бумаги
     *
     * @param mixed $data
     * @param bool $cache
     *
     * @return CustomStock|null
     */
    public static function search($data, bool $cache = true): ?CustomStock
    {
        try {
            // Проверка на валидность данных
            if (!self::isValidData($data)) {
                return null;
            }

            // Подготавливаем данные для создания или поиска
            $createData = is_object($data) ? [
                'symbol' => $data->getTicker(),
                'name' => $data->getName(),
                'user_id' => User::getAppUser($data->user_id),
                'currency_id' => $data->getCurrency(),
                'type_id' => $data->getCustomStockType(),
            ] : $data;

            // Формируем ключ для кэша
            $cacheKey = "custom-{$createData['user_id']}-{$createData['symbol']}";

            // Проверка в кэше
            if ($cache && Cache::tags([config('cache.tags')])->has($cacheKey)) {
                return Cache::tags([config('cache.tags')])->get($cacheKey);
            }

            // Поиск или создание кастомной бумаги
            $custom = self::firstOrCreate([
                'symbol' => $createData['symbol'],
                'user_id' => $createData['user_id'],
                'currency_id' => $createData['currency_id'],
                'type_id' => $createData['type_id'],
            ], $createData);

            // Сохранение в кэш
            Cache::tags([config('cache.tags')])->put($cacheKey, $custom, now()->addDay());

            return $custom;
        } catch (Exception $e) {
            LoggerHelper::getLogger('custom-search')->error($e->getMessage(), ['data' => $data, 'exception' => $e]);
            return null;
        }
    }


    /**
     * Проверка данных перед созданием кастомной бумаги
     *
     * @param $data
     *
     * @return bool
     */
    public static function isValidData($data): bool
    {
        // Проверяем, что $data — это массив или объект
        if (!is_array($data) && !is_object($data)) {
            LoggerHelper::getLogger('custom-stock')
                ->error('Переданный параметр не является массивом или объектом', ['data' => $data]);
            return false;
        }

        // Если $data — объект, проверяем наличие метода getTicker()
        if (is_object($data)) {
            if (!method_exists($data, 'getTicker')) {
                LoggerHelper::getLogger('custom-stock')
                    ->error('Переданный объект не содержит метода getTicker', ['data' => $data]);
                return false;
            }

            // Проверяем, что метод getTicker() возвращает значение
            if (empty($data->getTicker())) {
                LoggerHelper::getLogger('custom-stock')
                    ->error('Передан пустой тикер', ['data' => $data]);
                return false;
            }
        }

        // Если $data — массив, проверяем наличие ключа 'symbol' и его значение
        if (is_array($data) && !isset($data['symbol'])) {
            LoggerHelper::getLogger('custom-stock')
                ->error('Передан массив с отсутствующим или пустым символом тикера', ['data' => $data]);
            return false;
        }

        return true;
    }


    /**
     * @param $original
     * @param $text
     * @param $translitText
     * @param $foundStocks
     * @param $items
     * @param $condition
     * @param bool $async
     *
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
            [implode(' ', $splitedWords)],
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
                $items[] = $item->getItemData();
            }
        }
    }

    /**
     * @param $userId
     * @param $currency_id
     * @param $accountId
     * @param $classes
     *
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
     *
     * @return \Illuminate\Database\Eloquent\HigherOrderBuilderProxy|int|mixed
     */
    public function getLastPriceByDate($currency, $date = null)
    {
        try {
            //TODO используется класс из основного проекта, лучше бы как то переделать и вынести логику из метода

            //ищем все ID кастомных активов где может быть такой же каталог
            $ids = CustomStock::where('symbol', $this->symbol)
                ->pluck('id')
                ->toArray();

            $trade = ActiveTrade::whereHas('active', function ($query) use ($ids) {
                $query->where('item_type', $this->getMorphClass())
                    ->whereIn('item_id', $ids);
            })
                ->where('price', '>', 0)
                ->orderByDesc('trade_at')
                ->first();

            if ($trade) {
                if ($currency->id !== $trade->currency_id) {
                    return $currency->convert($trade->price, $trade->currency_id, $trade->trade_at);
                }


                return $trade->price;
            }

            return 0;
        } catch (Exception $e) {
            LoggerHelper::getLogger('convert')->error($e);
            LoggerHelper::getLogger('convert')->error('currency ID' . $currency->id);

            return 0;
        }
    }

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
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
     * @param $user
     * @param $collections
     *
     * @return void
     */
    public function selfRemoveData($user, $collections): void
    {
        $selfData = $this->whereUserId(config('app.env') . '-' . $user->id)->cursor();

        foreach ($selfData as $data) {
            $collections->put($this->getTableWithoutPrefix() . '.' . $data->id, json_encode($data));
        }
    }

    /**
     * @param $stock
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return bool
     * polymorhic method
     */
    public static function loadHistory($stock, Carbon $startDate, Carbon $endDate, $forceSkipCache = false)
    {
        //тк заранее все спаршено, будет заглушкой
    }

    /**
     * @param $stock
     *
     * @return void
     * polymorhic method
     */
    public static function loadCoupons($stock): void
    {
    }

    /**
     * @param $stock
     *
     * @return void
     * polymorhic method
     */
    public static function loadDividends($stock): void
    {
    }
}
