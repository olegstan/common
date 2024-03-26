<?php

namespace Common\Transformers\Catalog\TradingView;

use Common\Models\Catalog\TradingView\TradingViewKey;
use LaravelRest\Http\Transformers\BaseTransformer;

class TradingViewKeyTransformer extends BaseTransformer
{
    /**
     * @param TradingViewKey $model
     *
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'key' => $model->key,
            'ru' => $model->ru,
        ];

        return $this->withRelations($data, $model);
    }
}