<?php

namespace Common\Models\Catalog\Cbond;

use Carbon\Carbon;
use Common\Helpers\LoggerHelper;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Currency;
use Common\Models\Interfaces\Catalog\Cbond\DefinitionCbondConst;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogInterface;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;
use Common\Models\Traits\Catalog\Cbond\CbondRelationshipsTrait;
use Common\Models\Traits\Catalog\Cbond\CbondReturnGetDataFunc;
use Common\Models\Traits\Catalog\Cbond\CbondScopeTrait;
use Common\Models\Traits\Catalog\CommonCatalogTrait;
use Common\Models\Traits\Catalog\SearchActiveCatalogTrait;
use Exception;
use File;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class CbondStock
 *
 * @property $id
 * @property $secid
 * @property $shortname
 * @property $country
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
 * @property $exercisefee
 * @property $tv_ticker_id
 * @property $url
 * @property Collection|CbondCoupon[] $coupons
 *
 * @package Models\Catalog\Cbond
 */
class CbondStock extends BaseCatalog implements DefinitionCbondConst, CommonsFuncCatalogInterface
{
    //Связи с другими моделями
    use CbondRelationshipsTrait;

    //Возвращаемые данные для трансформеров, текущей сущности и тп
    use CbondReturnGetDataFunc;

    //функции запросов
    use CbondScopeTrait;

    //общие трейты
    use CommonCatalogTrait;

    //Общий трейт для каталогов и Актива для поиска бумаг
    use SearchActiveCatalogTrait;
    
    use HasFactory;

    /**
     * @var string
     */
    public $table = 'cbond_stocks';

    /**
     * @var array
     */
    protected $fillable = [
        'secid',
        'shortname',
        'regnumber',
        'name',
        'isin',//isin regs
        'isin144A',//isin 144A
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
        'issuedate',
        'matdate',
        'initialfacevalue',
        'faceunit',
        'latname',
        'startdatemoex',
        'earlyrepayment',
        'listlevel',
        'daystoredemption',
        'issuesize',
        'facevalue',
        'isqualifiedinvestors',
        'couponfrequency',
        'coupondate',
        'couponpercent',
        'couponvalue',
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
        'exercisefee',
        'tv_ticker_id',
        'url',
        'country',
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
     *
     */
    public function copyIsin144A()
    {
        /**
         * @var CbondStock $newCbond
         */
        $newCbond = $this->replicate();
        $newCbond->isin = $this->isin144A;
        $newCbond->secid = $this->isin144A;
        $newCbond->push();

        $newCbond->copyCouponsAndHistory($this);
    }

    /**
     * @param $cbond
     */
    public function copyCouponsAndHistory($cbond)
    {
        $cbondCoupons = CbondCoupon::where('cbond_stock_id', $cbond->id)
            ->get();

        /**
         * @var CbondCoupon $cbondCoupon
         */
        foreach ($cbondCoupons as $cbondCoupon)
        {
            $exists = CbondCoupon::where('cbond_stock_id', $this->id)
                ->whereDate('coupondate', $cbondCoupon->coupondate)
                ->first();

            if(!$exists)
            {
                $newCbondCoupon = $cbondCoupon->replicate();
                $newCbondCoupon->cbond_stock_id = $this->id;
                $newCbondCoupon->push();
            }else{
                $data = $cbondCoupon->toArray();
                $data['cbond_stock_id'] = $this->id;

                $exists->update($data);
            }
        }

        $cbondHistories = CbondHistory::where('cbond_stock_id', $cbond->id)
            ->get();

        foreach ($cbondHistories as $cbondHistory)
        {
            $exists = CbondHistory::where('cbond_stock_id', $this->id)
                ->whereDate('tradedate', $cbondHistory->tradedate)
                ->first();

            if(!$exists)
            {
                $newCbondHistory = $cbondHistory->replicate();
                $newCbondHistory->cbond_stock_id = $this->id;
                $newCbondHistory->push();
            }else{
                $data = $cbondHistory->toArray();
                $data['cbond_stock_id'] = $this->id;

                $exists->update($data);
            }
        }
    }

        /**
     * @param $userId
     * @param $currencyId
     * @param $accountId
     * @param $classes
     * @return mixed
     */
    public function createBindActive($userId, $currencyId, $accountId, $classes)
    {
        if (in_array($this->type, DefinitionCbondConst::BOND_VALUES)) {
            $bondCurrencyId = $this->getCurrency();

            $matDate = null;

            if($this->matdate)
            {
                $matDateCarbon = Carbon::createFromFormat('Y-m-d', $this->matdate)->startOfDay();
                $oldDateCarbon = Carbon::createFromFormat('Y-m-d', '1000-01-01');//очень старая дата для сравнения, если дата погашения больше чем она, значит дата валидна

                if($matDateCarbon->isValid() && $matDateCarbon->greaterThan($oldDateCarbon))
                {
                    $matDate = $matDateCarbon;
                }
            }

            return $classes['obligation']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::OBLIGATION_GROUP_TYPE,
                'buy_sum' => $this->facevalue,
                'buy_currency_id' => $bondCurrencyId,
                'buy_account_id' => $accountId,
                'sell_at' => $matDate,
                'rate_period_type_id' => $this->getCouponFrequency(),
                'rate' => $this->couponpercent,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if (in_array($this->type, DefinitionCbondConst::PIF_VALUES)) {
            return $classes['pif']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::STOCK_GROUP_TYPE,
                'buy_currency_id' => $currencyId,
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if (in_array($this->type, DefinitionCbondConst::FUTURES_VALUE)) {
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

        if (in_array($this->type, DefinitionCbondConst::ETF_VALUE)) {
            return $classes['etf']::create([
                'user_id' => $userId,
                'group_type_id' => DefinitionActiveConst::STOCK_GROUP_TYPE,
                'buy_currency_id' => $currencyId,
                'buy_account_id' => $accountId,
                'item_type' => $this->getMorphClass(),
                'item_id' => $this->id,
            ]);
        }

        if (in_array($this->type, DefinitionCbondConst::CURRENCY_VALUE)) {
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
        $history = $this->history()
            ->whereBetween($this->getDateField(), [$startDate, $endDate])
            ->pluck('close', $this->getDateField());

        if ($history) {
            return $history;
        }

        return false;
    }

    /**
     * @param Currency $currency
     * @param Carbon|null $date
     *
     * @return float|int
     */
    public function getLastPriceByDate(Currency $currency, Carbon $date = null)
    {
        try {
            /**
             * @var CbondHistory $history
             */
            $history = $this->history()
                ->when($date, function ($query) use ($date){
                    $query->whereDate($this->getDateField(), '<=', $date);
                })
                ->where('close', '>', 0)
                ->orderByDesc($this->getDateField())
                ->first();

            if($history)
            {
                $historyCurrency = Currency::getByCode($history->faceunit);
                if($historyCurrency)
                {
                    return $currency->convert($history->getValue(), $historyCurrency->id, $date);
                }

                return $history->getValue();
            }

            return 0;
        }catch (Exception $e){
            LoggerHelper::getLogger('convert')->error($e);
            LoggerHelper::getLogger('convert')->error('currency ID' . $currency->id);

            return 0;
        }
    }

    /**
     * @param bool $log
     * @return array|false
     */
    public static function diffDataWithMoex(bool $log = false)
    {
        try {
            $return = [];
            $logData = [];

            $moexStocks = MoscowExchangeStock::whereMarket('bonds')->get();

            foreach ($moexStocks as $moexStock) {
                $cbondStock = CbondStock::whereSecid($moexStock->isin)->first();

                if (!$cbondStock) {
                    continue;
                }

                $keys = [
                    'shortname',
                    'regnumber',
                    'name',
                    'is_traded',
                    'type',
                    'group',
                    'issuedate',
                    'matdate',
                    'initialfacevalue',
                    'startdatemoex',
                    'issuesize',
                    'facevalue',
                    'couponfrequency',
                    'couponpercent',
                    'typename',
                    'groupname',
                    'lotsize',
                ];

                foreach ($keys as $key) {
                    if ($cbondStock->$key === $moexStock->$key) {
                        continue;
                    }

                    $logData[$moexStock->isin]['cbond'] = $cbondStock;
                    $logData[$moexStock->isin]['moex'] = $moexStock;
                    $return[] = $cbondStock->url;
                    break;
                }

                break;
            }

            if ($log) {
                $path = resource_path() . DIRECTORY_SEPARATOR .
                    'CbondDiffData' . DIRECTORY_SEPARATOR .
                    Carbon::now()->format('Y-m-d');

                $fileName = $path . DIRECTORY_SEPARATOR . Carbon::now()->format('H-i-s') . '.json';

                if (!file_exists($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
                }

                File::put($fileName, json_encode($logData));
            }

            return $return;
        } catch (Exception $e) {
            LoggerHelper::getLogger('cbond-diff-moex')->error($e->getMessage);
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
    ) {
        $splitedWords = self::fullTextWildcards($text);

        $stocksQuery = self::selectRaw(
            '`cbond_stocks`.*, MATCH (`cbond_stocks`.`name`,`cbond_stocks`.`isin`,`cbond_stocks`.`latname`,`cbond_stocks`.`shortname`) AGAINST (?) as relevance',
            [implode(' ', $splitedWords)]
        )
            ->search($original, $text, $translitText);

        if ($condition) {
            $condition($stocksQuery);
        }

        /**
         * @var CbondStock[] $stocks
         */
        $stocks = $stocksQuery->get();

        if ($stocks) {
            foreach ($stocks as $item) {
                $items[] = $item->getItemData();
            }
        }
    }

    /**
     * @param $stock
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return true
     */
    public static function loadHistory($stock, Carbon $startDate, Carbon $endDate, $forceSkipCache = false)
    {
        //тк заранее все спаршено, будет заглушкой
        return true;
    }

    /**
     * @param $stock
     * @return void
     */
    public static function loadCoupons($stock): void
    {
        //тк заранее все спаршено, будет заглушкой
        return;
    }

    /**
     * @param $stock
     * @return void
     */
    public static function loadDividends($stock): void
    {
        //тк заранее все спаршено, будет заглушкой
        return;
    }
}
