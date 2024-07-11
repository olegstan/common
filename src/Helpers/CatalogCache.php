<?php
namespace Common\Helpers;

use Cache;
use Carbon\Carbon;
use Common\Models\Catalog\Cbond\CbondStock;
use Common\Models\Catalog\Currency\CbCurrency;
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
}