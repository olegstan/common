<?php

namespace App\src\Models\Traits\Catalog\Custom;

use App\src\Models\Interfaces\Catalog\DefinitionActiveConst;

use function App\Models\Traits\Custom\__;

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
    public function getCodeCurrency(): int
    {
        return $this->currency_id;
    }

    /**
     * @return array|string|null
     */
    public function getTypeText()
    {
        switch ($this->type_id)
        {
            case DefinitionActiveConst::STOCK:
                return __('model.moscow_exchange_stock.type_text.common_share');
            case DefinitionActiveConst::CURRENCY:
                return __('model.moscow_exchange_stock.type_text.currency');
            case DefinitionActiveConst::BOND:
            case DefinitionActiveConst::OBLIGATION:
                return __('model.moscow_exchange_stock.type_text.exchange_bond');
            case DefinitionActiveConst::FUTURES:
                return __('model.moscow_exchange_stock.type_text.futures');
            case DefinitionActiveConst::ETF:
            case DefinitionActiveConst::PIF:
            case DefinitionActiveConst::BPIF:
                return __('model.moscow_exchange_stock.type_text.etf_ppif');
            default:
                return __('model.yahoo_stock.type_text.default');
        }
    }
}
