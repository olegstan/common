<?php

namespace Common\Models\Interfaces\Catalog;

interface CommonsFuncCatalogHistoryInterface
{
    /**
     * @param $priceKey
     * @param $dateKey
     * @return mixed
     * данные будут храниться с тегом back, так как там идет обращение к ценам
     * и back загружает их
     */
    public function setPrice($priceKey, $dateKey);
}