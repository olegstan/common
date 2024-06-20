<?php

namespace Common\Models\Traits\Catalog\Yahoo;

use Common\Models\Interfaces\Catalog\DefinitionActiveConst;

trait YahooReturnGetDataFunc
{
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

    public function getDateField(): string
    {
        return 'date';
    }

    public function getValueField(): string
    {
        return 'close';
    }

    public function getSymbolField(): string
    {
        return 'symbol';
    }

    public function getCodeCurrency()
    {
        return $this->currency;
    }

    public function getCouponFrequency(): string
    {
        return '';
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getSecondSymbol(): string
    {
        return $this->symbol;
    }

    public function getLotSize(): int
    {
        return 1;
    }

    public function getStockName()
    {
        return $this->name;
    }

    public function getCouponPercent()
    {
        return null;
    }

    public function getMaturityDate()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getExchange(): string
    {
        return $this->exch_disp ?? '';
    }

    /**
     * @return string
     */
    public function getIsinField(): string
    {
        return 'symbol';
    }

    public function getName(): string
    {
        return $this->getType() . ' ' . $this->name . ' ' . $this->symbol;
    }

    public function getCatalog(): string
    {
        return DefinitionActiveConst::YAHOO_CATALOG;
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
        if (!isset($this->tradingview) || !$this->tradingview->country) {
            return '';
        }

        return $this->tradingview->country;
    }

    public function getIndustry(): string
    {
        if (!isset($this->tradingview) || !$this->tradingview->industry) {
            return '';
        }

        return $this->tradingview->industry;
    }

    public function getSector(): string
    {
        if (!isset($this->tradingview) || !$this->tradingview->sector) {
            return '';
        }

        return $this->tradingview->sector;
    }

    public function getCapitalization(): string
    {
        if (!isset($this->tradingview) || !$this->tradingview->capitalization) {
            return '';
        }

        return $this->tradingview->capitalization;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSymbolName(): string
    {
        return trim($this->name . ' ' . $this->symbol);
    }

    public function getUserId(): string
    {
        return '';
    }
}
