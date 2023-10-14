<?php

namespace Common\Models\Interfaces\Catalog;

use Carbon\Carbon;

interface CommonsFuncCatalogInterface
{
    public function getType(): int;

    public function getTypeText();

    public function getLotSize();

    public function getSymbol();

    public function getCurrency();

    public function getCodeCurrency();

    public function getSymbolField();

    public function getDateField();

    public function getCouponFrequency();

    public function createBindActive($userId, $currencyId, $accountId, $classes);

    public static function loadHistory($stock, Carbon $startDate, Carbon $endDate);

    public static function loadCoupons($stock): void;

    public static function loadDividends($stock): void;

}