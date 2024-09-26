<?php

namespace Common\Observers\Catalog\Custom;

use Common\Helpers\Catalog\CatalogSearch;
use Common\Helpers\LoggerHelper;
use Common\Models\Currency;
use Elasticsearch\ClientBuilder;
use Common\Models\Catalog\Custom\CustomStock;
use Exception;

class CustomStockObserver
{
    /**
     * @param CustomStock $model
     *
     * @return void
     */
    public function creating(CustomStock $model)
    {
        if (empty($model->currency_id) ||
            is_null($model->currency_id) ||
            $model->currency_id === '' ||
            $model->currency_id === 'RUR') {
            $model->currency_id = Currency::RUB;
        }
    }

    /**
     * @param CustomStock $model
     *
     * @return void
     */
    public function created(CustomStock $model)
    {
        CatalogSearch::indexRecordInElasticsearch($model, 'custom_stocks');
    }

    /**
     * @param CustomStock $model
     *
     * @return void
     */
    public function updated(CustomStock $model)
    {
    }

    /**
     * @param CustomStock $model
     *
     * @return void
     */
    public function deleted(CustomStock $model)
    {
        CatalogSearch::deleteFromIndex(CatalogSearch::CUSTOM_INDEX, $model->id);
    }

    /**
     * @param CustomStock $model
     *
     * @return void
     */
    public function deleting(CustomStock $model)
    {
    }
}