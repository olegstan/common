<?php
namespace Common\Helpers;

use Cache;
use Carbon\Carbon;
use Common\Models\Catalog\Cbond\CbondStock;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeStock;

class CatalogCache
{
    /**
     * @var CbondStock $parent
     */
   public static function getCbondItem($model)
   {
       return Cache::tags([config('cache.tags')])->remember('catalog.cbond.' . $model->cbond_stock_id, Carbon::now()->addDay(), function () use ($model)
       {
           return CbondStock::firstWhere('id', $model->cbond_stock_id);
       });
   }

    /**
     * @var MoscowExchangeStock $parent
     */
   public static function getMoexItem($model)
   {
       return Cache::tags([config('cache.tags')])->remember('catalog.moex.' . $model->moex_stock_id, Carbon::now()->addDay(), function () use ($model)
       {
           return MoscowExchangeStock::firstWhere('id', $model->moex_stock_id);
       });
   }
}