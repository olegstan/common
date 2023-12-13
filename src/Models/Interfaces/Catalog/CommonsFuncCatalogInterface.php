<?php

namespace Common\Models\Interfaces\Catalog;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface CommonsFuncCatalogInterface
{
    public function getType(): int;

    public function getTypeText();

    public function getLotSize();

    public function getSymbol();

    public function getCurrency();

    public function getCodeCurrency();

    public function getSymbolField();

    public function getDateField();//поле для определения даты из таблицы истории
    public function getValueField();//поле для определения значения из таблицы истории

    public function getCouponFrequency();
    public function getStockName();
    public function getMaturityDate();
    public function getCouponPercent();

    public function history(): HasMany;
    
    public function getLastPriceByDate($currency, $date = null);

    public function createBindActive($userId, $currencyId, $accountId, $classes);

    public static function loadHistory($stock, Carbon $startDate, Carbon $endDate);
    public function getPriceHistory(Carbon $startDate, Carbon $endDate);

    public static function loadCoupons($stock): void;

    public static function loadDividends($stock): void;

}