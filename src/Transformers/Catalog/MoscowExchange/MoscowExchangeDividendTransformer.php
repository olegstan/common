<?php

namespace Common\Transformers\Catalog\MoscowExchange;

use Common\Models\Catalog\MoscowExchange\MoscowExchangeDividend;
use Common\Models\Currency;
use LaravelRest\Http\Transformers\BaseTransformer;

class MoscowExchangeDividendTransformer extends BaseTransformer
{
    /**
     * @param MoscowExchangeDividend $model
     * @return array
     */
    public function transform($model)
    {
        $currencyId = null;
        if($currency = Currency::getByCode($model->currencyid))
        {
            $currencyId = $currency->id;
        }

        $data = [
            'id' => $model->id,
            'moex_stock_id' => $model->moex_stock_id,
            'registryclosedate' => $model->registryclosedate,
            'value' => $model->value,
            'currency_id' => $currencyId,
        ];

        return $this->withRelations($data, $model);
    }

}