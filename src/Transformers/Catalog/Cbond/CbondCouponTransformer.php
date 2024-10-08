<?php

namespace Common\Transformers\Catalog\Cbond;

use Cache;
use Common\Helpers\CatalogCache;
use Common\Models\Catalog\Cbond\CbondCoupon;
use Common\Models\Catalog\Cbond\CbondStock;
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
        $parent = CatalogCache::getCbondItem($model->cbond_stock_id);

        $currencyId = null;
        if($parent)
        {
            $currencyId = $parent->getCurrency();
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
            'value' => $model->getValue(),
            'valueprc' => $model->valueprc,
            'value_rub' => $model->value_rub,
            'morph' => $model->getMorphClass(),
        ];

        return $this->withRelations($data, $model);
    }

}