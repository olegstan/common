<?php

namespace Common\Models\Traits\Catalog\Yahoo;

use Common\Models\Interfaces\Catalog\DefinitionActiveConst;

trait YahooReturnGetDataFunc
{
    /**
     * @return string
     */
    public function getDateField(): string
    {
        return 'date';
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        if (in_array($this->type_disp, self::ETF_VALUE)) {
            return DefinitionActiveConst::ETF;
        }

        if (in_array($this->type_disp, self::CURRENCY_VALUE)) {
            return DefinitionActiveConst::CURRENCY;
        }

        return DefinitionActiveConst::STOCK;
    }

    /**
     * @return string
     */
    public function getSymbolField(): string
    {
        return 'symbol';
    }

    /**
     * @return mixed
     */
    public function getCodeCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getTypeText(): string
    {
        switch ($this->type_disp) {
            case 'ETF':
//                return __('model.yahoo_stock.type_text.etf');
                return 'ETF';
            case 'Currency':
                return 'Валюта';
//                return __('model.yahoo_stock.type_text.currency');
            default:
                return 'Акции';
//                return __('model.yahoo_stock.type_text.default');
        }
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
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @return int
     */
    public function getLotSize(): int
    {
        return 1;
    }

    /**
     * @return mixed
     */
    public function getStockName()
    {
        return $this->name;
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
}
