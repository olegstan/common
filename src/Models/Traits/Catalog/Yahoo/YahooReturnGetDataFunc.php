<?php

namespace App\src\Models\Traits\Catalog\Yahoo;

use App\src\Models\Interfaces\Catalog\DefinitionActiveConst;

use function App\Models\Traits\Yahoo\__;

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
    public function getType()
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
     * @return array|string|null
     */
    public function getTypeText()
    {
        switch ($this->type_disp) {
            case 'ETF':
                return __('model.yahoo_stock.type_text.etf');
            case 'Currency':
                return __('model.yahoo_stock.type_text.currency');
            default:
                return __('model.yahoo_stock.type_text.default');
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
}
