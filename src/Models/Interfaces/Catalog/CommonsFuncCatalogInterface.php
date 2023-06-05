<?php

namespace App\src\Models\Interfaces\Catalog;

interface CommonsFuncCatalogInterface
{
    public function getTypeText();

    public function getLotSize();

    public function getSymbol();

    public function getCurrency();

    public function getCodeCurrency();

    public function getSymbolField();

    public function getDateField();

    public function getCouponFrequency();

}