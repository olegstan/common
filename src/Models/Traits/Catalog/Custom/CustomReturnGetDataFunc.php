<?php

namespace Common\Models\Traits\Catalog\Custom;

use Common\Models\Interfaces\Catalog\DefinitionActiveConst;

trait CustomReturnGetDataFunc
{
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
        return 'symbol';
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
        return 'price';
    }

    /**
     * @return int
     */
    public function getLotSize(): int
    {
        return $this->lotsize ?: 1;
    }

    /**
     * @return string
     */
    public function getSymbol():string
    {
        return $this->symbol ?? $this->name;
    }

    /**
     * @return string
     */
    public function getSecondSymbol():string
    {
        return $this->name ?? $this->symbol;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type_id;
    }

    /**
     * @return int
     */
    public function getCodeCurrency()
    {
        return $this->currency_id;
    }

    /**
     * @return string
     */
    public function getTypeText(): string
    {
        switch ($this->type_id)
        {
            case DefinitionActiveConst::CURRENCY:
                return 'Валюта';
//                return __('model.moscow_exchange_stock.type_text.currency');
            case DefinitionActiveConst::BOND:
            case DefinitionActiveConst::OBLIGATION:
                return 'Биржевая облигация';
//                return __('model.moscow_exchange_stock.type_text.exchange_bond');
            case DefinitionActiveConst::FUTURES:
                return 'Фьючерс';
//                return __('model.moscow_exchange_stock.type_text.futures');
            case DefinitionActiveConst::ETF:
            case DefinitionActiveConst::PIF:
            case DefinitionActiveConst::BPIF:
                return 'ETF';
//                return __('model.moscow_exchange_stock.type_text.etf_ppif');
            default:
                return 'Акции';
//                return __('model.yahoo_stock.type_text.default');
        }
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

    /**
     * @return string
     */
    public function getIsinField(): string
    {
        return 'char_code';
    }

    /**
     * @return string
     */
    public function getExchange(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getType() . ' ' . $this->name . ' ' . $this->symbol;
    }

    public function getCatalog(): string
    {
        return DefinitionActiveConst::CUSTOM_CATALOG;
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
        $name = $this->name === $this->symbol ? $this->name : $this->name . ' ' . $this->symbol;
        return trim($name);
    }

    public function getUserId()
    {
        return $this->user_id;
    }
}
