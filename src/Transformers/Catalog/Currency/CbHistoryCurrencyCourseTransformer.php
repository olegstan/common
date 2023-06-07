<?php

namespace Common\Transformers\Catalog\Currency;

use Common\Models\Catalog\Currency\CbHistoryCurrencyCourse;
use LaravelRest\Http\Transformers\BaseTransformer;

class CbHistoryCurrencyCourseTransformer extends BaseTransformer
{
    /**
     * @param CbHistoryCurrencyCourse $model
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'currency_id' => $model->currency_id,
            'value' => $model->value,
            'nominal' => $model->nominal,
            'date' => $model->date,
        ];

        return $this->withRelations($data, $model);
    }

}