<?php
namespace Common\Helpers;

use Cache;
use Carbon\Carbon;
use Common\Models\Catalog\Cbond\CbondStock;
use Common\Models\Catalog\Currency\CbCurrency;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeSplit;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeStock;

class CatalogCache
{
    /**
     * @var $id
     */
   public static function getCbondItem($id)
   {
       return Cache::tags([config('cache.tags')])->remember('catalog.cbond.' . $id, Carbon::now()->addDay(), function () use ($id)
       {
           return CbondStock::firstWhere('id', $id);
       });
   }

    /**
     * @var $id
     */
   public static function getMoexItem($id)
   {
       return Cache::tags([config('cache.tags')])->remember('catalog.moex.' . $id, Carbon::now()->addDay(), function () use ($id)
       {
           return MoscowExchangeStock::firstWhere('id', $id);
       });
   }

    /**
     * @var $code
     */
   public static function getCbCurrency($code)
   {
       return Cache::tags([config('cache.tags')])->remember('catalog.cb.' . $code, Carbon::now()->addDay(), function () use ($code)
       {
           return CbCurrency::firstWhere('char_code', $code);
       });
   }

    /**
     * @param MoscowExchangeStock $stock
     * @param $lotsize
     * @param Carbon|null $date
     */
    public static function getMoexSplit($stock, &$lotsize, Carbon $date = null)
    {
        if($date && $stock)
        {
//            SELECT *
//            FROM moscow_exchange_splits AS mes1
//JOIN moscow_exchange_splits AS mes2
//ON mes1.moex_stock_id = mes2.moex_stock_id
//        AND mes1.date <> mes2.date
//        AND DATE_FORMAT(mes1.date, '%Y-%m') = DATE_FORMAT(mes2.date, '%Y-%m')

            if(!$stock->created_at)
            {
                LoggerHelper::getLogger('debug')->info('no date for moex stock by SECID '. $stock->secid);
                return;
            }


            $dateFormatted = $date->format('Y-m-d');
            $cacheKey = "moex_last_split_{$stock->id}_$dateFormatted";

            $finalLotSize = Cache::tags([config('cache.tags')])->remember($cacheKey, 60 * 60, static function () use ($date, $stock) {
                // Assuming initial lot size based on the stock's current lot size or default value
                $createdDate = $stock->created_at;

                $initialLotSize = $stock->lotsize ?: 1;

                if($date->gt($createdDate) || $createdDate->isSameDay($date))
                {
                    //TODO Retrieve splits from the database
                    $splits = MoscowExchangeSplit::where('moex_stock_id', $stock->id)
                        ->whereDate('date', '>=', $createdDate->format('Y-m-d'))
                        ->orderBy('date')
                        ->get();
                }else{
                    $splits = MoscowExchangeSplit::where('moex_stock_id', $stock->id)
                        ->whereDate('date', '<=', $createdDate->format('Y-m-d'))
                        ->whereDate('date', '>=', $date->format('Y-m-d'))
                        ->orderBy('date')
                        ->get();

                    $currentLotSize = $initialLotSize;

                    // Process each split
                    foreach ($splits as $split) {
                        $before = $split->before;
                        $after = $split->after;

                        $currentLotSize = $currentLotSize * ($after * $before);
                    }

                    return $currentLotSize;
                }
            });

            $lotsize = $finalLotSize;
        }
    }
}