<?php

namespace Common\Transformers\Catalog\MoscowExchange;

use Cache;
use Carbon\Carbon;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeCoupon;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeStock;
use Common\Models\Currency;
use LaravelRest\Http\Transformers\BaseTransformer;

class MoscowExchangeCouponTransformer extends BaseTransformer
{
    /**
     * @param MoscowExchangeCoupon $model
     * @return array
     */
    public function transform($model)
    {
        /**
         * @var MoscowExchangeStock $parent
         */
        $parent = Cache::remember('moex.' . $model->moex_stock_id, Carbon::now()->addDay(), function () use ($model)
        {
            return MoscowExchangeStock::firstWhere('id', $model->moex_stock_id);
        });

        $currencyId = null;
        if($parent)
        {
            $json = json_decode($parent->faceunit);

            if(count($json))
            {
                if($currency = Currency::getByCode($json[0]))
                {
                    $currencyId = $currency->id;
                }
            }
        }

        $data = [
            'id' => $model->id,
            'moex_stock_id' => $model->moex_stock_id,
            'name' => $model->name,
            'issuevalue' => $model->issuevalue,
            'coupondate' => $model->coupondate,
            'recorddate' => $model->recorddate,
            'startdate' => $model->startdate,
            'initialfacevalue' => $model->initialfacevalue,
            'currency_id' => $currencyId,
            'value' => $model->getValue(),
            'valueprc' => $model->valueprc,
            'value_rub' => $model->value_rub,
            'morph' => $model->getMorphClass(),
        ];

        return $this->withRelations($data, $model);
    }

}