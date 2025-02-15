<?php

namespace Common\Models\Catalog\Yahoo;

use Cache;
use Carbon\Carbon;
use Common\Helpers\Catalog\CatalogSearch;
use Common\Helpers\Curls\Yahoo\YahooCurl;
use Common\Helpers\HistoryHelper;
use Common\Helpers\LoggerHelper;
use Common\Jobs\Base\CreateJobs;
use Common\Jobs\Exchanges\YahooDataJob;
use Common\Jobs\Exchanges\YahooJob;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Currency;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogInterface;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;
use Common\Models\Interfaces\Catalog\Yahoo\DefinitionYahooConst;
use Common\Models\Traits\Catalog\CommonCatalogTrait;
use Common\Models\Traits\Catalog\SearchActiveCatalogTrait;
use Common\Models\Traits\Catalog\Yahoo\YahooRelationshipsTrait;
use Common\Models\Traits\Catalog\Yahoo\YahooReturnGetDataFunc;
use Common\Models\Traits\Catalog\Yahoo\YahooScopeTrait;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Scheb\YahooFinanceApi\ApiClientFactory;
use Throwable;

/**
 * @property $id
 * @property $symbol
 * @property $name
 * @property $exch
 * @property $type
 * @property $exch_disp
 * @property $type_disp
 * @property $currency
 * @property $icons
 * @property $sector
 * @property $industry
 * @property $country
 * @property $state
 * @property $city
 * @property $exchange
 * @property $tv_ticker_id
 */
class YahooStock extends BaseCatalog implements DefinitionYahooConst, CommonsFuncCatalogInterface
{
    //Связи с другими моделями
    use YahooRelationshipsTrait;

    //Возвращаемые данные для трансформеров, текущей сущности и тп
    use YahooScopeTrait;

    //функции запросов
    use YahooReturnGetDataFunc;

    //общие трейты
    use CommonCatalogTrait;

    //Общий трейт для каталогов и Актива для поиска бумаг
    use SearchActiveCatalogTrait;
    
    use HasFactory;

    /**
     * @var string
     */
    public $table = 'yahoo_stocks';

    /**
     * @var array
     */
    protected $fillable = [
        'symbol',
        'name',
        'exch',
        'type',
        'exch_disp',
        'type_disp',
        'currency',
        'icons',
        'sector',
        'industry',
        'country',
        'state',
        'city',
        'exchange',
        'tv_ticker_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'symbol' => 'string',
        'name' => 'string',
        'exch' => 'string',
        'type' => 'string',
        'exch_disp' => 'string',
        'type_disp' => 'string',
        'currency' => 'string',
        'icons' => 'string',
        'sector' => 'string',
        'industry' => 'string',
        'country' => 'string',
        'state' => 'string',
        'city' => 'string',
        'exchange' => 'string',
        'tv_ticker_id' => 'integer',
    ];

    public $timestamps = false;

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
     * @throws Throwable
     */
    public static function createAndGet(
        $original,
        $text,
        $translitText,
        $foundStocks = null,
        &$items,
        $condition = null,
        bool $async = true
    ) {
        $symbolIds = [];
        $queueIds = [];

        if ($foundStocks) {
            foreach ($foundStocks as $foundStock) {
                $symbolIds[$foundStock['symbol']] = $foundStock['symbol'];
            }

            $stockQuery = self::whereIn('symbol', $symbolIds)
                ->get()
                ->keyBy('symbol')
                ->toArray();


            try {
                $client = ApiClientFactory::createApiClient();
                $quotes = $client->getQuotes($symbolIds);

                $currency = [];
                foreach ($quotes as $quote) {
                    $currency[$quote->getSymbol()] = $quote->getCurrency();
                }
            }catch (Exception $e)
            {
                LoggerHelper::getLogger()->error($e);
            }

            foreach ($foundStocks as $foundStock) {
                try {
                    if (!isset($stockQuery[$foundStock['symbol']])) {
                        try {
                            $data = $foundStock['currency']; // Входные данные могут быть как JSON, так и обычной строкой

// Проверяем, является ли входная строка JSON
                            if ($decodedData = json_decode($data, true))
                            {
                                // Если это JSON, используем его
                                $foundStock['currency'] = reset($decodedData);
                            }
                        }catch (Exception $e){
                            LoggerHelper::getLogger()->error(var_export($foundStock, true));
                        }

                        $createdStock = self::create($foundStock);

                        if ($createdStock && CatalogSearch::isElasticsearchHealthy()) {
                            // Индексируем созданную запись в Elasticsearch
                            CatalogSearch::indexRecordInElasticsearch($createdStock, 'yahoo_stocks');

                            $queueIds[] = $createdStock->id;
                        }
                    }
                } catch (Exception $e) {
                    LoggerHelper::getLogger()->error($e, $foundStock);
                }
            }
        }

        if ($queueIds) {
            if ($async) {
                CreateJobs::create(YahooJob::class, [$queueIds]);
            } else {
                (new YahooJob())->fire(null, [$queueIds]);
            }
            CreateJobs::create(YahooDataJob::class, [$queueIds]);
        }

        $splitedWords = self::fullTextWildcards($text);
        $stocksQuery = self::with('tradingview')
            ->selectRaw(
                '
                    `yahoo_stocks`.*,
                    MATCH (`yahoo_stocks`.`symbol`, `yahoo_stocks`.`name`) AGAINST (?) as relevance,
                    `tv_tickers`.`average_volume`
                ',
                [implode(' ', $splitedWords)],
            )
            ->leftJoin('tv_tickers', function ($query) {
                $query->on('tv_tickers.symbol', '=', 'yahoo_stocks.symbol')
                    ->where('tv_tickers.exchange', '!=', 'MOEX');
            })
            ->search($original, $text, $translitText)
            ->orderByRaw('`tv_tickers`.`average_volume` DESC')
            ->orderByRaw('relevance DESC');

        if ($condition) {
            $condition($stocksQuery);
        }

        $stocks = $stocksQuery->limit(10)
            ->get();

        if ($stocks) {
            /**
             * @var YahooStock[] $stocks
             */
            foreach ($stocks as $item) {
                $items[] = $item->getItemData();
            }
        }
    }

    /**
     * @param $userId
     * @param $currencyId
     * @param $accountId
     * @param $classes
     *
     * @return mixed
     */
    public function createBindActive($userId, $currencyId, $accountId, $classes)
    {
        if (in_array($this->type, DefinitionYahooConst::FUTURES_VALUE)) {
            $active = $classes['futures']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::INSTRUMENT_CASH_FLOW_GROUP_TYPE,
                'buy_currency_id' => $this->getCurrency(),
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if (in_array($this->type, DefinitionYahooConst::ETF_VALUE)) {
            $active = $classes['etf']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::STOCK_GROUP_TYPE,
                'buy_currency_id' => $this->getCurrency(),
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if (in_array($this->type, DefinitionYahooConst::CURRENCY_VALUE)) {
            $active = $classes['currency']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::INSTRUMENT_CASH_FLOW_GROUP_TYPE,
                'buy_currency_id' => $this->getCurrency(),
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if (!isset($active)) {
            $active = $classes['stock']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::STOCK_GROUP_TYPE,
                'buy_currency_id' => $this->getCurrency(),
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        return $active;
    }

    /**
     * @param Currency $currency
     * @param Carbon|null $date
     *
     * @return float|int|mixed
     */
    public function getLastPriceByDate(Currency $currency, Carbon $date = null)
    {
        try {
            /**
             * @var YahooHistory $history
             */
            $history = $this->history()
                ->when($date, function ($query) use ($date) {
                    $query->whereDate($this->getDateField(), '<=', $date);
                })
                ->orderByDesc($this->getDateField())
                ->first();

            if ($history) {
                $historyCurrency = Currency::getByCode($this->currency);
                if ($historyCurrency) {
                    return $currency->convert($history->getValue(), $historyCurrency->id, $date);
                }

                return $history->getValue();
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
            ->pluck('close', $this->getDateField());

        if ($history) {
            return $history;
        }

        return false;
    }

    /**
     * @param $stock
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param false $forceSkipCache
     * @return bool
     */
    public static function loadHistory($stock, Carbon $startDate, Carbon $endDate, $forceSkipCache = false): bool
    {
        return HistoryHelper::load($stock, $startDate, $endDate, $forceSkipCache);
    }

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array|bool
     */
    public function requestDataFromApi(Carbon $startDate, Carbon $endDate)
    {
        return YahooCurl::getHistoricalData($this->symbol, YahooCurl::INTERVAL_1_DAY, $startDate, $endDate);
    }

    /**
     * @param $data
     */
    public function saveDataFromApi($data)
    {
        $history = YahooHistory::where('date', '=', $data['date']->format('Y-m-d'))
            ->where('yahoo_stock_id', $this->id)
            ->first();

        if (!$history) {
            YahooHistory::create([
                'date' => $data['date']->format('Y-m-d'),
                'open' => $data['open'],
                'high' => $data['high'],
                'low' => $data['low'],
                'close' => $data['close'],
                'adj_close' => $data['adj_close'],
                'volume' => $data['volume'],
                'yahoo_stock_id' => $this->id,
            ]);
        }
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
     */
    public static function loadDividends($stock): void
    {
        $data = YahooCurl::getDividends($stock->symbol);

        foreach ($data as $datum) {
            YahooDividend::whereDate('date', $datum['date'])
                ->where('yahoo_stock_id', $stock->id)
                ->firstOrCreate([
                    'yahoo_stock_id' => $stock->id,
                    'date' => $datum['date'],
                    'value' => $datum['value'],
                ]);
        }
    }

    /**
     * @param $stock
     *
     * @return void
     */
    public static function loadSplits($stock): void
    {
        $data = YahooCurl::getSplits($stock->symbol);

        foreach ($data as $datum) {
            YahooSplit::whereDate('date', $datum['date'])
                ->where('yahoo_stock_id', $stock->id)
                ->firstOrCreate([
                    'yahoo_stock_id' => $stock->id,
                    'date' => $datum['date'],
                    'before' => $datum['before'],
                    'after' => $datum['after'],
                ]);
        }
    }
}

