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
     * @param $stock
     * @param $lotsize
     * @param Carbon|null $date
     */
    public static function getMoexSplit($stock, &$lotsize, Carbon $date = null)
    {
        if($date && $stock)
        {
            if(!$stock->issuedate)
            {
                LoggerHelper::getLogger('debug')->info('no date for moex stock by SECID '. $stock->secid);
            }

            $dateFormatted = $date->format('Y-m-d');
            $cacheKey = "moex_last_split_{$stock->id}_$dateFormatted";

            $finalLotSize = Cache::tags([config('cache.tags')])->remember($cacheKey, 60 * 60, static function () use ($dateFormatted, $stock) {
                $issueDate = Carbon::createFromFormat('Y-m-d', $stock->issuedate);
                $initialLotSize = $stock->lotsize ?: 1; // предположим, что исходная лотность хранится в created_at_lotsize
                $splits = MoscowExchangeSplit::where('moex_stock_id', $stock->id)
                    ->whereDate('date', '>=', $issueDate->format('Y-m-d'))
                    ->whereDate('date', '<=', $dateFormatted)
                    ->orderBy('date')
                    ->get();

                $currentLotSize = $initialLotSize;
                foreach ($splits as $split) {
                    $currentLotSize = $currentLotSize * ($split->after / $split->before);
                }

                return $currentLotSize;
            });

            $lotsize = $finalLotSize;
        }
    }
}