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
        // Инициализация Elasticsearch клиента
        $client = ClientBuilder::create()
            ->setHosts(config('elasticsearch.config.hosts'))
            ->build();

        try {
            // Удаление записи из индекса Elasticsearch
            $client->delete([
                'index' => 'catalog.custom_stocks', // Укажите индекс
                'id' => $model->id, // Используйте идентификатор записи
            ]);

            LoggerHelper::getLogger()
                ->info("Запись с ID $model->id была удалена из индекса Elasticsearch.");
        } catch (Exception $e) {
            // Логируем ошибку
            LoggerHelper::getLogger()
                ->error("Ошибка при удалении записи с ID $model->id из индекса Elasticsearch: " . $e->getMessage());
        }
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