<?php

namespace Common\Models\Interfaces\Catalog;

use Carbon\Carbon;

interface CommonsFuncCatalogHistoryInterface
{
    public function setPrice($priceKey, $dateKey);
}