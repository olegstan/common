<?php

namespace Common\Transformers\Catalog\TradingView;

use Common\Models\Catalog\TradingView\TradingViewYear;
use LaravelRest\Http\Transformers\BaseTransformer;

class TradingViewYearTransformer extends BaseTransformer
{
    /**
     * @param TradingViewYear $model
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'key_id' => $model->key_id,
            'year' => $model->year,
            'value' => $model->value,
            'percent' => $model->percent,
            'ticker_id' => $model->ticker_id,
            'key_text' => isset($model->key) ? $model->key->key : '',
            'ru_text' => isset($model->key) ? $model->key->ru : '',
        ];

        $model->unsetRelation('key');

        return $this->withRelations($data, $model);
    }
}