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
     * @return int
     */
    public function getLotSize(): int
    {
        return 1;
    }

    /**
     * @return string
     */
    public function getSymbol():string
    {
        return $this->symbol ?? $this->name;
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
}
