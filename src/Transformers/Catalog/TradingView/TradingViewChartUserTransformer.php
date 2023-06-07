<?php

namespace Common\Transformers\Catalog\TradingView;

use App\Models\TradingView\TradingViewChartUser;
use LaravelRest\Http\Transformers\BaseTransformer;

class TradingViewChartUserTransformer extends BaseTransformer
{
    /**
     * @param TradingViewChartUser $model
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'ticker_id' => $model->ticker_id,
            'user_id' => $model->user_id,
        ];

        return $this->withRelations($data, $model);
    }
}