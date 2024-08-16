<?php

namespace Common\Models\Interfaces\Catalog;

interface CommonsFuncCatalogHistoryInterface
{
    /**
     * @param $priceKey
     * @param $dateKey
     * @param $catalog
     * @return mixed
     * данные будут храниться с тегом back, так как там идет обращение к ценам
     * и back загружает их
     * третий параметр каталог, так как там нужны данные о номинале, если это бумага является облигацией, то цена является % от номинала
     */
    public function setPrice($priceKey, $dateKey, $catalog);
}