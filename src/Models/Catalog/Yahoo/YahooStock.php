<?php

namespace Common\Models\Catalog\Yahoo;

use Common\Helpers\Curls\Yahoo\YahooCurl;
use Common\Helpers\LoggerHelper;
use Common\Jobs\YahooDataJob;
use Common\Jobs\YahooJob;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;
use Common\Models\Interfaces\Catalog\Yahoo\DefinitionYahooConst;
use Common\Models\Traits\Catalog\CommonCatalogTrait;
use Common\Models\Traits\Catalog\Yahoo\YahooRelationshipsTrait;
use Common\Models\Traits\Catalog\Yahoo\YahooReturnGetDataFunc;
use Carbon\Carbon;
use Cache;
use Exception;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogInterface;
use Common\Models\Traits\Catalog\Yahoo\YahooScopeTrait;
use Illuminate\Support\Facades\Queue;
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

            foreach ($foundStocks as $foundStock) {
                try {
                    if (!isset($stockQuery[$foundStock['symbol']])) {
                        $createdStock = self::create($foundStock);

                        if ($createdStock) {
                            $descriptionData = YahooCurl::getQuotes([$createdStock->symbol]);

                            if (is_array($descriptionData)) {
                                foreach ($descriptionData as $datum) {
                                    if (isset($datum['symbol'], $datum['currency']) && $datum['symbol'] === $createdStock->symbol && !empty($datum['currency'])) {
                                        $createdStock->currency = $datum['currency'];
                                        $createdStock->save();
                                    }
                                }
                            }

                            $queueIds[] = $createdStock->id;
                        }
                    }
                } catch (Exception $e) {
                    LoggerHelper::getLogger()->error($e);
                }
            }
        }

        if ($queueIds) {
            if ($async) {
                Queue::push(YahooJob::class, [$queueIds]);
            } else {
                (new YahooJob())->fire(null, [$queueIds]);
            }
            Queue::push(YahooDataJob::class, [$queueIds]);
        }

        $splitedWords = self::fullTextWildcards($text);
        $stocksQuery = self::with('tradingview')
            ->selectRaw(
                '
                    `yahoo_stocks`.*,
                    MATCH (`yahoo_stocks`.`symbol`, `yahoo_stocks`.`name`) AGAINST (?) as relevance,
                    `tv_tickers`.`average_volume`
                ',
                [implode(' ', $splitedWords)]
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
                $typeId = $item->getType();

                $items[] = [
                    'id' => $item->id,
                    'name' => $item->name . ' ' . $item->symbol,
                    'type_id' => $typeId,
                    'type_text' => $item->getTypeText(),
                    'currency_id' => $item->getCurrency(),
                    'ticker' => 'catalog.2',
                    'facevalue' => '',
                    'couponfrequency' => $item->getCouponFrequency(),
                    'coupondate' => '',
                    'couponpercent' => '',
                    'couponvalue' => '',
                    'decimals' => '',
                    'lotsize' => $item->getLotSize(),
                    'symbol' => $item->getSymbol(),
                    'country' => $item->tradingview ? $item->tradingview->country : '',
                    'industry' => $item->tradingview ? $item->tradingview->industry : '',
                    'sector' => $item->tradingview ? $item->tradingview->sector : '',
                    'capitalization' => $item->tradingview ? $item->tradingview->capitalization : '',
                ];
            }
        }
    }

    /**
     * @param $userId
     * @param $accountId
     * @param $classes
     * @return mixed
     */
    public function createBindActive($userId, $accountId, $classes)
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

        $this->active($active)->save($active);

        return $active;
    }

    /**
     * @param $stock
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return bool
     */
    public static function loadHistory($stock, Carbon $startDate, Carbon $endDate): bool
    {
        $key = $startDate->format('Y-m-d') . ' / ' . $endDate->format('Y-m-d');

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        /**
         * @var YahooStock $stock
         */
        $data = YahooCurl::getHistoricalData($stock->symbol, YahooCurl::INTERVAL_1_DAY, $startDate, $endDate);

        if ($data) {
            Cache::add($key, true, Carbon::now()->addDay());

            foreach ($data as $datum) {
                $history = YahooHistory::where('date', '=', $datum['date']->format('Y-m-d'))
                    ->where('symbol', $stock->symbol)
                    ->first();

                if (!$history) {
                    YahooHistory::create([
                        'symbol' => $stock->symbol,
                        'date' => $datum['date']->format('Y-m-d'),
                        'open' => $datum['open'],
                        'high' => $datum['high'],
                        'low' => $datum['low'],
                        'close' => $datum['close'],
                        'adj_close' => $datum['adj_close'],
                        'volume' => $datum['volume'],
                    ]);
                }
            }

            return true;
        }

        Cache::add($key, false, Carbon::now()->addDay());
        LoggerHelper::getLogger()->info('No any history for ' . $stock->symbol);

        return false;
    }
}

