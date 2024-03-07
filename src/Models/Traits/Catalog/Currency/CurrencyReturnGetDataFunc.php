<?php

namespace Common\Models\Traits\Catalog\Currency;

use Common\Models\Interfaces\Catalog\DefinitionActiveConst;

trait CurrencyReturnGetDataFunc
{
    /**
     * @return string
     */
    public function getDateField(): string
    {
        return 'date';
    }


    /**
     * @return string
     */
    public function getValueField(): string
    {
        return 'value';
    }

    /**
     * @return string
     */
    public function getTypeText(): string
    {
        return '';
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->char_code;
    }

    /**
     * @return string
     */
    public function getCouponFrequency(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getSymbolField(): string
    {
        return 'currency_id';
    }

    /**
     * @return mixed
     */
    public function getSymbol()
    {
        return $this->char_code;
    }

    /**
     * @return mixed
     */
    public function getCodeCurrency()
    {
        return $this->char_code;
    }

    /**
     * @return int
     */
    public function getLotSize(): int
    {
        return 1;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return DefinitionActiveConst::CURRENCY;
    }

    /**
     * @return mixed
     */
    public function getStockName()
    {
        return $this->char_code;
    }

    /**
     * @return null
     */
    public function getCouponPercent()
    {
        return null;
    }

    /**
     * @return null
     */
    public function getMaturityDate()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getExchange(): string
    {
        return 'MOEX';
    }
}
