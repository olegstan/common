<?php

namespace Common\Transformers\Catalog\TradingView;

use Common\Models\Catalog\TradingView\TradingViewTicker;
use LaravelRest\Http\Transformers\BaseTransformer;

class TradingViewTickerTransformer extends BaseTransformer
{
    /**
     * @param TradingViewTicker $model
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'symbol' => $model->symbol,
            'description' => $model->description,
            'exchange' => $model->exchange,
            'provider_id' => $model->provider_id,
            'country' => $model->country,
            'capitalization' => $model->capitalization,
            'typespecs' => $model->typespecs,
            'industry_id' => $model->industry_id,
            'type' => $model->type,
            'point_value' => $model->point_value,
            'exchange_web' => $model->exchange_web,
            'listed_exchange' => $model->listed_exchange,
            'currency' => $model->currency,
            'tick_size' => $model->tick_size,
            'sector' => $model->sector,
            'industry' => $model->industry,
            'timezone' => $model->timezone,
            'session' => json_decode($model->session),
            'icon' => $model->getIcon(),
        ];

        return $this->withRelations($data, $model);
    }
}