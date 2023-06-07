<?php

namespace Common\Transformers\Catalog\Currency;

use Common\Models\Catalog\Currency\CbCurrency;
use LaravelRest\Http\Transformers\BaseTransformer;

class CbCurrencyTransformer extends BaseTransformer
{
    /**
     * @param CbCurrency $model
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'cb_id' => $model->cb_id,
            'num_code' => $model->num_code,
            'char_code' => $model->char_code,
            'symbol' => $model->getSymbol(),
            'nominal' => $model->nominal,
            'name' => $model->name,
            'lotsize' => 1,
            'icon' => $model->getIcon(),
            'type' => 'currency',
            'ticker' => $model->getMorphClass(),
        ];

        return $this->withRelations($data, $model);
    }

}