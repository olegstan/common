<?php

namespace Common\Transformers\Catalog\TradingView;

use LaravelRest\Http\Transformers\BaseTransformer;

class TradingViewQuarterTransformer extends BaseTransformer
{
    /**
     * @param TradingViewQuarter $model
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'key_id' => $model->key_id,
            'quarter' => $model->quarter,
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