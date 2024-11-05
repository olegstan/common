<?php

namespace Common\Models\Interfaces\Catalog;

use Carbon\Carbon;

interface CommonsFuncCatalogInterface
{
    public function getType(): int;

    public function getTypeText();

    public function getLotSize(Carbon $date = null): int;//стало понятно что данное поле должно быть привязано к дате, так как лотность может меняться, в зависимости от даты

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

    public function getIsinField();

    public function getName();

    public function getExchange();

    public function history();

    public function getLastPriceByDate($currency, $date = null);

    public function createBindActive($userId, $currencyId, $accountId, $classes);

    public static function loadHistory($stock, Carbon $startDate, Carbon $endDate, $forceSkipCache = false);

    public function getPriceHistory(Carbon $startDate, Carbon $endDate);

    public static function loadCoupons($stock): void;

    public static function loadDividends($stock): void;

    public function getId();

    public function getCatalog();

    public function getFaceValue();

    public function getCouponDate();

    public function getCouponValue();

    public function getDecimals();

    public function getCountry();

    public function getIndustry();

    public function getSector();

    public function getCapitalization();

    public function getUserId();
    public function getNumberCatalog(): int;

    public function getNumberCatalog(): int;
}