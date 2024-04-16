<?php

namespace Common\Models\Catalog\MoscowExchange;

use Cache;
use Carbon\Carbon;
use Common\Helpers\Curls\MoscowExchange\MoscowExchangeCurl;
use Common\Helpers\LoggerHelper;
use Common\Jobs\MoscowExchangeDataJob;
use Common\Jobs\MoscowExchangeJob;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Catalog\Finex\FinexHistory;
use Common\Models\Currency;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogInterface;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;
use Common\Models\Interfaces\Catalog\MoscowExchange\DefinitionMoexConst;
use Common\Models\Traits\Catalog\CommonCatalogTrait;
use Common\Models\Traits\Catalog\MoscowExchange\MoexRelationshipsTrait;
use Common\Models\Traits\Catalog\MoscowExchange\MoexReturnGetDataFunc;
use Common\Models\Traits\Catalog\MoscowExchange\MoexScopeTrait;
use Common\Models\Traits\Catalog\SearchActiveCatalogTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Throwable;

/**
 * Class MoscowExchangeStock
 *
 * @property $id
 * @property $secid
 * @property $shortname
 * @property $regnumber
 * @property $name
 * @property $isin
 * @property $is_traded
 * @property $emitent_id
 * @property $emitent_title
 * @property $emitent_inn
 * @property $emitent_okpo
 * @property $gosreg
 * @property $type
 * @property $group
 * @property $primary_boardid
 * @property $marketprice_boardid
 * @property $issuedate
 * @property $matdate
 * @property $initialfacevalue
 * @property $faceunit
 * @property $latname
 * @property $startdatemoex
 * @property $earlyrepayment
 * @property $listlevel
 * @property $daystoredemption
 * @property $issuesize
 * @property $facevalue
 * @property $isqualifiedinvestors
 * @property $couponfrequency
 * @property $coupondate
 * @property $couponpercent
 * @property $couponvalue
 * @property $typename
 * @property $groupname
 * @property $market_id
 * @property $market
 * @property $engine_id
 * @property $engine
 * @property $decimals
 * @property $lotsize
 * @property $icons
 * @property $expiration
 * @property $boardid
 * @property $prevsettleprice
 * @property $minstep
 * @property $lasttradedate
 * @property $sectype
 * @property $assetcode
 * @property $prevopenposition
 * @property $lotvolume
 * @property $initialmargin
 * @property $highlimit
 * @property $lowlimit
 * @property $stepprice
 * @property $lastsettleprice
 * @property $prevprice
 * @property $imtime
 * @property $buysellfee
 * @property $scalperfee
 * @property $negotiatedfee
 * @property Collection|MoscowExchangeCoupon[] $coupons
 * @property Collection|MoscowExchangeDividend[] $dividends
 *
 * @package Models\Catalog\MoscowExchange
 */
class MoscowExchangeStock extends BaseCatalog implements DefinitionMoexConst, CommonsFuncCatalogInterface
{
    //Связи с другими моделями
    use MoexRelationshipsTrait;

    //Возвращаемые данные для трансформеров, текущей сущности и тп
    use MoexReturnGetDataFunc;

    //функции запросов
    use MoexScopeTrait;

    //общие трейты
    use CommonCatalogTrait;

    //Общий трейт для каталогов и Актива для поиска бумаг
    use SearchActiveCatalogTrait;

    /**
     * @var string
     */
    public $table = 'moscow_exchange_stocks';

    /**
     * @var array
     */
    protected $fillable = [
        'secid',
        'shortname',
        'regnumber',
        'name',
        'isin',
        'is_traded',
        'emitent_id',
        'emitent_title',
        'emitent_inn',
        'emitent_okpo',
        'gosreg',
        'type',
        'group',
        'primary_boardid',
        'marketprice_boardid',

        'issuedate',//Дата начала торгов
        'matdate',//Дата погашения
        'initialfacevalue',//Первоначальная номинальная стоимость
        'faceunit',//Валюта номинала
        'latname',//Английское наименование
        'startdatemoex',//Дата начала торгов на Московской Бирже
        'earlyrepayment',
        'listlevel',
        'daystoredemption',//Дней до погашения
        'issuesize',//Объем выпуска
        'facevalue',
        'isqualifiedinvestors',//Бумаги для квалифицированных инвесторов
        'couponfrequency',//Периодичность выплаты купона в год
        'coupondate',//Дата выплаты купона
        'couponpercent',//Ставка купона, %
        'couponvalue',//Сумма купона, в валюте номинала
        'typename',
        'groupname',

        'market_id',
        'market',
        'engine_id',
        'engine',
        'decimals',
        'lotsize',
        'icons',

        'expiration',
        'boardid',
        'prevsettleprice',
        'minstep',
        'lasttradedate',
        'sectype',
        'assetcode',
        'prevopenposition',
        'lotvolume',
        'initialmargin',
        'highlimit',
        'lowlimit',
        'stepprice',
        'lastsettleprice',
        'prevprice',
        'imtime',
        'buysellfee',
        'scalperfee',
        'negotiatedfee',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'secid' => 'string',
        'shortname' => 'string',
        'regnumber' => 'string',
        'name' => 'string',
        'isin' => 'string',
        'is_traded' => 'string',
        'emitent_id' => 'string',
        'emitent_title' => 'string',
        'emitent_inn' => 'string',
        'emitent_okpo' => 'string',
        'gosreg' => 'string',
        'type' => 'string',
        'group' => 'string',
        'primary_boardid' => 'string',
        'marketprice_boardid' => 'string',

        'issuedate' => 'string',
        'matdate' => 'string',
        'initialfacevalue' => 'float',
        'faceunit' => 'string',
        'latname' => 'string',
        'startdatemoex' => 'string',
        'earlyrepayment' => 'boolean',
        'listlevel' => 'integer',
        'daystoredemption' => 'integer',
        'issuesize' => 'integer',
        'facevalue' => 'float',
        'isqualifiedinvestors' => 'boolean',
        'couponfrequency' => 'integer',
        'coupondate' => 'string',
        'couponpercent' => 'float',
        'couponvalue' => 'float',
        'typename' => 'string',
        'groupname' => 'string',

        'market_id' => 'integer',
        'market' => 'string',
        'engine_id' => 'integer',
        'engine' => 'string',
        'decimals' => 'integer',
        'lotsize' => 'integer',
        'icons' => 'string',

        'expiration' => 'string',
        'boardid' => 'string',
        'prevsettleprice' => 'double',
        'minstep' => 'double',
        'lasttradedate' => 'date',
        'sectype' => 'string',
        'assetcode' => 'string',
        'prevopenposition' => 'integer',
        'lotvolume' => 'integer',
        'initialmargin' => 'double',
        'highlimit' => 'double',
        'lowlimit' => 'double',
        'stepprice' => 'double',
        'lastsettleprice' => 'double',
        'prevprice' => 'double',
        'imtime' => 'date',
        'buysellfee' => 'double',
        'scalperfee' => 'double',
        'negotiatedfee' => 'double',
        'exercisefee' => 'double',
    ];

    public $timestamps = false;

    /**
     * @param $userId
     * @param $currency_id
     * @param $accountId
     * @param $classes
     * @return mixed
     */
    public function createBindActive($userId, $currencyId, $accountId, $classes)
    {
        if (in_array($this->type, DefinitionMoexConst::BOND_VALUES)) {
            $bondCurrencyId = $this->getCurrency();

            return $classes['obligation']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::OBLIGATION_GROUP_TYPE,
                'buy_sum' => $this->facevalue,
                'buy_currency_id' => $bondCurrencyId,
                'buy_account_id' => $accountId,
                'sell_at' => $this->matdate ? Carbon::createFromFormat('Y-m-d', $this->matdate)->startOfDay() : null,
                'rate_period_type_id' => $this->getCouponFrequency(),
                'rate' => $this->couponpercent,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if (in_array($this->type, DefinitionMoexConst::PIF_VALUES)) {
            return $classes['pif']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::STOCK_GROUP_TYPE,
                'buy_currency_id' => $currencyId,
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if (in_array($this->type, DefinitionMoexConst::FUTURES_VALUE)) {
            return $classes['futures']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::INSTRUMENT_CASH_FLOW_GROUP_TYPE,
                'buy_currency_id' => $currencyId,
                'buy_account_id' => $accountId,
                'sell_at' => $this->expiration ? Carbon::createFromFormat('Y-m-d', $this->expiration) : null,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if (in_array($this->type, DefinitionMoexConst::ETF_VALUE)) {
            return $classes['etf']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::STOCK_GROUP_TYPE,
                'buy_currency_id' => $currencyId,
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if (in_array($this->type, DefinitionMoexConst::CURRENCY_VALUE)) {
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
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return false
     */
    public function getPriceHistory(Carbon $startDate, Carbon $endDate)
    {
        $history = $this->finexHistory()
            ->whereBetween($this->getDateField(), [$startDate, $endDate])
            ->pluck('close', 'tradedate');

        if ($history) {
            return $history;
        }

        $history = $this->history()
            ->whereBetween($this->getDateField(), [$startDate, $endDate])
            ->pluck('close', 'tradedate');

        if ($history) {
            return $history;
        }

        return false;
    }

    /**
     * @param null $date
     * @return float
     */
    public function getLastPriceByDate($currency, $date = null)
    {
        /**
         * @var FinexHistory $history
         */
        $query = $this->finexHistory();

        if ($date) {
            $query->whereDate($this->getDateField(), '<=', $date);
        }

        $history = $query->where('close', '>', 0)
            ->orderByDesc($this->getDateField())
            ->first();

        if ($history) {
            $historyCurrency = Currency::getByCode($history->faceunit);
            if ($historyCurrency) {
                return $currency->convert($history->getValue(), $historyCurrency->id, $date);
            }

            return $history->getValue();
        }

        /**
         * @var MoscowExchangeHistory $history
         */
        $query = $this->history();

        if ($date) {
            $query->whereDate($this->getDateField(), '<=', $date);
        }

        $history = $query->where('close', '>', 0)
            ->orderByDesc($this->getDateField())
            ->first();

        if ($history) {
            $historyCurrency = Currency::getByCode($history->faceunit);
            if ($historyCurrency) {
                return $currency->convert($history->getValue(), $historyCurrency->id, $date);
            }

            return $history->getValue();
        }

        return 0;
    }

    /**
     * @param null $foundStocks
     * @param bool $async
     * @throws Throwable
     */
    public static function createBeforeGet($foundStocks = null, $async = true)
    {
        $secIds = [];

        $queueIds = [];
        if ($foundStocks) {
            foreach ($foundStocks as $foundStock) {
                $secIds[$foundStock['secid']] = $foundStock['secid'];
            }

            $stockQuery = self::whereIn('secid', $secIds)
                ->get()
                ->keyBy('secid')
                ->toArray();

            foreach ($foundStocks as $foundStock) {
                try {
                    if (!isset($stockQuery[$foundStock['secid']])) {
                        $stock = self::where('secid', $foundStock['secid'])->first();

                        if ($stock) {
                            continue;
                        }

                        /**
                         * @var MoscowExchangeStock $createdStock
                         */
                        $createdStock = self::create($foundStock);

                        if ($createdStock) {
                            $createdStock->saveData();

                            $queueIds[] = $createdStock->id;
                        }
                    }
                } catch (Throwable $e) {
                    LoggerHelper::getLogger()->error($e);
                    LoggerHelper::getLogger()->error('move forward');
                }
            }
        }

        if ($queueIds) {
            if ($async) {
                Queue::push(MoscowExchangeJob::class, [$queueIds]);
            } else {
                (new MoscowExchangeJob())->fire(null, [$queueIds]);
            }

            Queue::push(MoscowExchangeDataJob::class, [$queueIds]);
        }
    }

    /**
     * @param $original
     * @param $text
     * @param $translitText
     * @param $foundStocks
     * @param $items
     * @param $condition
     * @param $async
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
        $async = true
    ) {
        MoscowExchangeStock::createBeforeGet($foundStocks, $async);

        $splitedWords = self::fullTextWildcards($text);

        $stocksQuery = self::with('tradingview')
            ->selectRaw(
                '
                    `moscow_exchange_stocks`.*, 
                    MATCH (`moscow_exchange_stocks`.`secid`,`moscow_exchange_stocks`.`name`,`moscow_exchange_stocks`.`isin`,`moscow_exchange_stocks`.`latname`,`moscow_exchange_stocks`.`shortname`) AGAINST (?) as relevance,
                    `tv_tickers`.`average_volume`
                ',
                [implode(' ', $splitedWords)]
            )
            ->leftJoin('tv_tickers', function ($query) {
                //чтобы соединить тикеры московской биржи нужно сделать замену -RM на ''
                //так как тикеры в tradingview сопвадают без этой строкки
                $query->on('tv_tickers.symbol', '=', DB::raw('REPLACE(`moscow_exchange_stocks`.`secid`, "-RM", "")'))
                    ->where('tv_tickers.exchange', '=', 'MOEX');
            })
            ->search($original, $text, $translitText)
            //->where('is_traded', '=', 1)//пусть все инструменты будут находиться чтобы не возникало коллизий
            ->orderByRaw('`tv_tickers`.`average_volume` DESC')
            ->orderByRaw(
                "CASE
                    WHEN `moscow_exchange_stocks`.`listlevel` = 1 && `moscow_exchange_stocks`.`type` = 'common_share' THEN 1000
                    WHEN `moscow_exchange_stocks`.`listlevel` = 1 && `moscow_exchange_stocks`.`type` = 'preferred_share' THEN 999
                    WHEN `moscow_exchange_stocks`.`listlevel` = 1 && `moscow_exchange_stocks`.`type` = 'stock_dr' THEN 998
                    WHEN `moscow_exchange_stocks`.`listlevel` = 2 && `moscow_exchange_stocks`.`type` = 'common_share' THEN 997
                    WHEN `moscow_exchange_stocks`.`listlevel` = 2 && `moscow_exchange_stocks`.`type` = 'preferred_share' THEN 996
                    WHEN `moscow_exchange_stocks`.`listlevel` = 2 && `moscow_exchange_stocks`.`type` = 'stock_dr' THEN 995
                    WHEN `moscow_exchange_stocks`.`listlevel` = 3 && `moscow_exchange_stocks`.`type` = 'common_share' THEN 994
                    WHEN `moscow_exchange_stocks`.`listlevel` = 3 && `moscow_exchange_stocks`.`type` = 'preferred_share' THEN 993
                    WHEN `moscow_exchange_stocks`.`listlevel` = 3 && `moscow_exchange_stocks`.`type` = 'stock_dr' THEN 992
                    WHEN `moscow_exchange_stocks`.`listlevel` = 1 THEN 100
                    WHEN `moscow_exchange_stocks`.`listlevel` = 2 THEN 99
                    WHEN `moscow_exchange_stocks`.`listlevel` = 3 THEN 98
                    WHEN `moscow_exchange_stocks`.`type` = 'common_share' THEN 10
                    WHEN `moscow_exchange_stocks`.`type` = 'preferred_share' THEN 9
                    WHEN `moscow_exchange_stocks`.`type` = 'stock_dr' THEN 8
                    WHEN `moscow_exchange_stocks`.`type` = 'futures' THEN 1
                    WHEN `moscow_exchange_stocks`.`type` = 'cb_bond' THEN 2
                    WHEN `moscow_exchange_stocks`.`type` = 'subfederal_bond' THEN 2
                    WHEN `moscow_exchange_stocks`.`type` = 'municipal_bond' THEN 2
                    WHEN `moscow_exchange_stocks`.`type` = 'euro_bond' THEN 2
                    WHEN `moscow_exchange_stocks`.`type` = 'state_bond' THEN 2
                    WHEN `moscow_exchange_stocks`.`type` = 'ifi_bond' THEN 2
                    WHEN `moscow_exchange_stocks`.`type` = 'exchange_bond' THEN 2
                    WHEN `moscow_exchange_stocks`.`type` = 'corporate_bond' THEN 2
                    WHEN `moscow_exchange_stocks`.`type` = 'ofz_bond' THEN 2
                    WHEN `moscow_exchange_stocks`.`type` = 'non_exchange_bond' THEN 2
                    WHEN `moscow_exchange_stocks`.`type` = 'exchange_ppif' THEN 2
                    WHEN `moscow_exchange_stocks`.`type` = 'private_ppif' THEN 2
                    WHEN `moscow_exchange_stocks`.`type` = 'public_ppif' THEN 2
                    WHEN `moscow_exchange_stocks`.`type` = 'interval_ppif' THEN 2
                    WHEN `moscow_exchange_stocks`.`type` = 'option' THEN 1
                    WHEN `moscow_exchange_stocks`.`type` = 'depositary_receipt' THEN 1
                    ELSE 0
                END DESC"
            )
            ->orderByRaw('relevance DESC');


        if ($condition) {
            $condition($stocksQuery);
        }

        /**
         * @var MoscowExchangeStock[] $stocks
         */
        $stocks = $stocksQuery->get();

        if ($stocks) {
            foreach ($stocks as $item) {
                $items[] = $item->getItemData();
            }
        }
    }

    /**
     * @return void
     */
    public function saveData()
    {
        $descriptionData = MoscowExchangeCurl::getDescription($this->secid);

        if (!$descriptionData) {
            $descriptionData = MoscowExchangeCurl::getDescription($this->isin);
        }

        if ($descriptionData) {
            if (isset($descriptionData['faceunit']) && !empty($descriptionData['faceunit'])) {
                $descriptionData['faceunit'] = json_encode([$descriptionData['faceunit']]);
            }

            if (!isset($descriptionData['faceunit']) && isset($descriptionData['currencyid']) && !empty($descriptionData['currencyid'])) {
                $descriptionData['faceunit'] = json_encode([$descriptionData['currencyid']]);
            }

            if (isset($descriptionData['lstdeldate']) && !empty($descriptionData['lstdeldate'])) {
                $descriptionData['expiration'] = $descriptionData['lstdeldate'];
            }

            $this->fill($descriptionData);
        }

        $boardData = MoscowExchangeCurl::getBoards($this->secid);

        if ($boardData) {
            $saveBoard = [
                'market_id' => $boardData['market_id'],
                'market' => $boardData['market'],
                'engine_id' => $boardData['engine_id'],
                'engine' => $boardData['engine'],
                'decimals' => $boardData['decimals'],
            ];

            if (!isset($descriptionData['faceunit']) && isset($boardData['currencyid']) && !empty($boardData['currencyid'])) {
                $saveBoard['faceunit'] = json_encode([$boardData['currencyid']]);
            }

            $this->fill($saveBoard);
        }

        $cureencyData = MoscowExchangeCurl::getCurrency($this->secid);

        if ($cureencyData && !isset($descriptionData['faceunit'])) {
            $this->fill(['faceunit' => json_encode($cureencyData)]);
        }

        $data = MoscowExchangeCurl::getData($this);

        if ($data && isset($data['lotsize']) && empty($this->lotsize)) {
            $this->fill(['lotsize' => $data['lotsize']]);
        }

        if ($this->market === 'bonds' && (empty($this->faceunit) || empty($this->couponpercent) || empty($this->couponvalue))) {
            $coupons = MoscowExchangeCurl::getCoupons($this->secid);

            if ($coupons && is_array($coupons) && count($coupons) > 0) {
                $saveData = [];

                if (empty($this->faceunit) && isset($coupons[0]['faceunit']) && !empty($coupons[0]['faceunit'])) {
                    $saveData['faceunit'] = json_encode([$coupons[0]['faceunit']]);
                }

                if (empty($this->couponpercent) && isset($coupons[0]['valueprc']) && !empty($coupons[0]['valueprc'])) {
                    $saveData['couponpercent'] = $coupons[0]['valueprc'];
                }

                //бывает что valueprc может быть 0 у первой записи
                //в этом случае можно проверить

                //TODO XS2388941580 нет вообще ни процента, ни суммы выплаты
                if (!isset($saveData['couponpercent']) && isset($coupons[0]['value']) && isset($coupons[0]['facevalue']) && $coupons[0]['value'] > 0 && $coupons[0]['facevalue'] > 0) {
                    $saveData['couponpercent'] = $coupons[0]['value'] / $coupons[0]['facevalue'] * 100;
                }

                if (empty($this->couponvalue) && isset($coupons[0]['value']) && !empty($coupons[0]['value'])) {
                    $saveData['couponvalue'] = $coupons[0]['value'];
                }

                $this->fill($saveData);
            }
        }

        if (empty($this->lotsize)) {
            $this->lotsize = 1;

            //TODO: Тк парсера тинька тут нет, придумать что-то другое

//            $lot = TinkoffCurl2::getBondLots($this->secid);
//
//            if (isset($lot) && !empty($lot))
//            {
//                $this->fill(['lotsize' => $lot]);
//            }
        }

        if ($this->engine === 'futures') {
            $dataFutures = MoscowExchangeCurl::getFutures($this->secid, $this->market);

            if ($dataFutures && is_array($dataFutures) && count($dataFutures) > 0) {
                if (isset($dataFutures[0]['lastdeldate'])) {
                    $dataFutures[0]['expiration'] = $dataFutures[0]['lastdeldate'];
                }

                $this->fill($dataFutures[0]);
            }
        }

        $this->save();
    }

    /**
     * @param $stock
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return bool
     */
    public static function loadHistory($stock, Carbon $startDate, Carbon $endDate)
    {
        [$bool, $result] = self::cacheHistory($stock, $startDate, $endDate);

        if ($bool) {
            return $result;
        }

        /**
         * @var MoscowExchangeStock $stock
         */
        $data = MoscowExchangeCurl::getHistory($stock, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));

        if ($data) {
            Cache::tags(['catalog'])->add($result, true, Carbon::now()->addDay());

            foreach ($data as $datum) {
                $history = MoscowExchangeHistory::where('tradedate', '=', $datum['tradedate'])
                    ->where('moex_stock_id', $stock->id)
                    ->first();

                if (!$history &&
                    isset($datum['open'], $datum['low'], $datum['close'], $datum['high']) &&
                    $datum['open'] !== '' &&
                    $datum['low'] !== '' &&
                    $datum['close'] !== '' &&
                    $datum['high'] !== '') {
                    if ($stock->market === 'bonds') {
                        $datum['open'] = $stock->facevalue * $datum['open'] / 100;
                        $datum['low'] = $stock->facevalue * $datum['low'] / 100;
                        $datum['close'] = $stock->facevalue * $datum['close'] / 100;
                        $datum['high'] = $stock->facevalue * $datum['high'] / 100;

                        if (isset($datum['marketprice2']) && is_numeric($datum['marketprice2'])) {
                            $datum['marketprice2'] = $stock->facevalue * $datum['marketprice2'] / 100;
                        }
                        if (isset($datum['marketprice3']) && is_numeric($datum['marketprice3'])) {
                            $datum['marketprice3'] = $stock->facevalue * $datum['marketprice3'] / 100;
                        }
                    }

                    $datum['moex_stock_id'] = $stock->id;
                    MoscowExchangeHistory::create($datum);
                }
            }

            return true;
        }

        Cache::tags(['catalog'])->add($result, false, Carbon::now()->addDay());
        LoggerHelper::getLogger()->info('No any history for ' . $stock->secid);

        return false;
    }

    /**
     * @param $stock
     * @return void
     */
    public static function loadCoupons($stock): void
    {
        $data = MoscowExchangeCurl::getCoupons($stock->secid);

        if ($data) {
            foreach ($data as $datum) {
                $coupon = MoscowExchangeCoupon::where('coupondate', '=', $datum['coupondate'])
                    ->where('moex_stock_id', $stock->id)
                    ->first();

                if (!$coupon) {
                    $datum['moex_stock_id'] = $stock->id;

                    if (empty($datum['recorddate'])) {
                        $datum['recorddate'] = null;
                    }

                    MoscowExchangeCoupon::create($datum);
                }

                if ($coupon && $coupon->value === 0 && $datum['value'] != 0) {
                    $coupon->value = $datum['value'];
                }
            }
        } else {
            LoggerHelper::getLogger()->info('No any coupon for ' . $stock->secid);
        }
    }

    /**
     * @param $stock
     * @return void
     */
    public static function loadDividends($stock): void
    {
        $data = MoscowExchangeCurl::getDividends($stock->secid);

        if ($data) {
            foreach ($data as $datum) {
                $dividend = MoscowExchangeDividend::where('registryclosedate', '=', $datum['registryclosedate'])
                    ->where('moex_stock_id', $stock->id)
                    ->first();

                if (!$dividend) {
                    $datum['moex_stock_id'] = $stock->id;

                    if (empty($datum['registryclosedate'])) {
                        $datum['registryclosedate'] = null;
                    }

                    MoscowExchangeDividend::create($datum);
                }

                if ($dividend && $dividend->value === 0 && $datum['value'] != 0) {
                    $dividend->value = $datum['value'];
                }
            }
        } else {
            LoggerHelper::getLogger()->info('No any dividend for ' . $stock->secid);
        }
    }
}