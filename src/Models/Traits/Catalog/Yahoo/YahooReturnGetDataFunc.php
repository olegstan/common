<?php

namespace Common\Models\Traits\Catalog\Yahoo;

use Carbon\Carbon;
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
        return 'close';
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
     * @return string
     */
    public function getSecondSymbol(): string
    {
        return $this->symbol;
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

    /**
     * @return mixed
     */
    public function getStockName()
    {
        $name = trim($this->name);
        $symbol = trim($this->symbol);

        if($name)
        {
            return $name;
        }

        if($symbol)
        {
            return $symbol;
        }
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

    /**
     * @return string
     */
    public function getName(): string
    {
        return trim(
            ($this->getType() ? $this->getType() . ' ' : '') .
            ($this->name ? $this->name . ' ' : '') .
            ($this->symbol ? $this->symbol : '')
        );
    }

    /**
     * @return string
     */
    public function getCatalog(): string
    {
        return DefinitionActiveConst::YAHOO_CATALOG;
    }

    /**
     * @return string
     */
    public function getFaceValue(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getCouponDate(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getCouponValue(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getDecimals(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        if (!isset($this->tradingview) || !$this->tradingview->country) {
            return '';
        }

        return $this->tradingview->country;
    }

    /**
     * @return string
     */
    public function getIndustry(): string
    {
        if (!isset($this->tradingview) || !$this->tradingview->industry) {
            return '';
        }

        return $this->tradingview->industry;
    }

    /**
     * @return string
     */
    public function getSector(): string
    {
        if (!isset($this->tradingview) || !$this->tradingview->sector) {
            return '';
        }

        return $this->tradingview->sector;
    }

    /**
     * @return string
     */
    public function getCapitalization(): string
    {
        if (!isset($this->tradingview) || !$this->tradingview->capitalization) {
            return '';
        }

        return $this->tradingview->capitalization;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSymbolName(): string
    {
        return trim($this->name . ' ' . $this->symbol);
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return '';
    }

    /**
     * Возвращает номер каталога
     *
     * @return int
     */
    public function getNumberCatalog(): int
    {
        return DefinitionActiveConst::YAHOO_QUOTES;
    }
}
