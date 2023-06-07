<?php

namespace Common\Transformers;

use Common\Models\Currency;
use LaravelRest\Http\Transformers\BaseTransformer;

class CurrencyTransformer extends BaseTransformer
{
    /**
     * @param Currency $model
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'name' => $model->name,
            'code' => $model->code,
            'sign' => $model->sign ? $model->sign : $model->code,
            'order' => $model->order,
        ];

        return $this->withRelations($data, $model);
    }

}