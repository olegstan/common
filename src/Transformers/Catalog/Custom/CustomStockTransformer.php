<?php

namespace Common\Transformers\Catalog\Custom;

use Common\Models\Catalog\Custom\CustomStock;
use LaravelRest\Http\Transformers\BaseTransformer;

class CustomStockTransformer extends BaseTransformer
{
    /**
     * @param CustomStock $model
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'name' => $model->name,
            'type_id' => $model->type_id,
            'currency_id' => $model->currency_id,
            'symbol' => $model->getSymbol(),
            'type_text' => $model->getTypeText(),
            'facevalue' => $model->facevalue,
            'matdate' => $model->matdate,
            'rate_period_type_id' => $model->rate_period_type_id,
            'rate' => $model->rate,
            'lotsize' => $model->getLotSize(),
            'ticker' => $model->getMorphClass(),
        ];

        return $this->withRelations($data, $model);
    }
}