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
     * @param $id
     * @param $lotsize
     * @param Carbon|null $date
     */
   public static function getMoexSplit($id, &$lotsize, Carbon $date = null)
   {
       if($date)
       {
           $dateFormatted = $date->format('Y-m-d');
           $cacheKey = "moex_last_split_{$id}_$dateFormatted";

           $split = Cache::remember($cacheKey, 60 * 60, static function () use ($dateFormatted, $id) {
               return MoscowExchangeSplit::where('moex_stock_id', $id)
                   ->whereDate('date', '<=', $dateFormatted)
                   ->orderByDesc('date')
                   ->first() ?: 'empty';
           });

           if ($split !== 'empty') {
               $lotsize = $split->after;
           }
       }
   }
}