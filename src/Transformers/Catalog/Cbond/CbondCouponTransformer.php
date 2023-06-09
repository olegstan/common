<?php

namespace Common\Transformers\Catalog\Cbond;

use App\Models\Catalog\Cbond\CbondCoupon;
use Cache;
use Carbon\Carbon;
use Common\Models\Catalog\Cbond\CbondStock;
use Common\Models\Currency;
use LaravelRest\Http\Transformers\BaseTransformer;

class CbondCouponTransformer extends BaseTransformer
{
    /**
     * @param CbondCoupon $model
     * @return array
     */
    public function transform($model)
    {
        /**
         * @var CbondStock $parent
         */
        $parent = Cache::remember('cbond.' . $model->cbond_stock_id, Carbon::now()->addDay(), function () use ($model)
        {
            return CbondStock::firstWhere('id', $model->cbond_stock_id);
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
            'cbond_stock_id' => $model->cbond_stock_id,
            'name' => $model->name,
            'issuevalue' => $model->issuevalue,
            'coupondate' => $model->coupondate,
            'recorddate' => $model->recorddate,
            'startdate' => $model->startdate,
            'initialfacevalue' => $model->initialfacevalue,
            'currency_id' => $currencyId,
            'value' => $model->value,
            'valueprc' => $model->valueprc,
            'value_rub' => $model->value_rub,
        ];

        return $this->withRelations($data, $model);
    }

}