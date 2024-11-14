<?php

namespace Common\Transformers\Catalog\Yahoo;

use Carbon\Carbon;
use Common\Models\Catalog\Yahoo\YahooStock;
use LaravelRest\Http\Transformers\BaseTransformer;

class YahooStockTransformer extends BaseTransformer
{
    /**
     * @param YahooStock $model
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'symbol' => $model->getSymbol(),
            'name' => $model->getStockName(),
            'exch' => $model->exch,
            'type' => $model->type,
            'exch_disp' => $model->exch_disp,
            'type_disp' => $model->type_disp,
            'lotsize' => $model->getLotSize(Carbon::now()),
            'icon' => $model->getIcon(),
            'ticker' => $model->getMorphClass(),
        ];

        return $this->withRelations($data, $model);
    }

}