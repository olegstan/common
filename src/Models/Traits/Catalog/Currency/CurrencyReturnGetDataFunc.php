<?php

namespace Common\Models\Traits\Catalog\Currency;

use Carbon\Carbon;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;

trait CurrencyReturnGetDataFunc
{
    public function getDateField(): string
    {
        return 'date';
    }

    public function getValueField(): string
    {
        return 'value';
    }

    public function getTypeText(): string
    {
        return 'Валюта';
    }

    public function getName()
    {
        return $this->char_code;
    }

    public function getCouponFrequency(): string
    {
        return '';
    }

    public function getSymbolField(): string
    {
        return 'currency_id';
    }

    public function getSymbol()
    {
        return $this->char_code;
    }

    public function getSecondSymbol()
    {
        return $this->char_code;
    }

    public function getCodeCurrency()
    {
        return $this->char_code;
    }

    /**
     * Возвращает лотность бумаги
     *
     * @param Carbon|null $date
     *
     * @return int
     */
    public function getLotSize(Carbon $date = null): int
    {
        return 1;
    }

    public function getType(): int
    {
        return DefinitionActiveConst::CURRENCY;
    }

    public function getStockName()
    {
        return $this->char_code;
    }

    public function getCouponPercent()
    {
        return null;
    }

    public function getMaturityDate()
    {
        return null;
    }

    public function getExchange(): string
    {
        return 'MOEX';
    }

    public function getIsinField(): string
    {
        return 'char_code';
    }

    public function getCatalog(): string
    {
        return DefinitionActiveConst::CB_CATALOG;
    }

    public function getFaceValue(): string
    {
        return '';
    }

    public function getCouponDate(): string
    {
        return '';
    }

    public function getCouponValue(): string
    {
        return '';
    }

    public function getDecimals(): string
    {
        return '';
    }

    public function getCountry(): string
    {
        return '';
    }

    public function getIndustry(): string
    {
        return '';
    }

    public function getSector(): string
    {
        return '';
    }

    public function getCapitalization(): string
    {
        return '';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSymbolName(): string
    {
        return $this->char_code . ' - ' . $this->name;
    }

    public function getUserId(): string
    {
        return '';
    }
}