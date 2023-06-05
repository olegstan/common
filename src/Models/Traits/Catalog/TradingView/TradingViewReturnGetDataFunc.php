<?php

namespace App\src\Models\Traits\Catalog\TradingView;

use function App\Models\Traits\TradingView\__;

trait TradingViewReturnGetDataFunc
{
    /**
     * @return string
     */
    public function getCouponFrequency(): string
    {
        return '';
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
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @return array|string|null
     */
    public function getTypeText()
    {
        switch ($this->type)
        {
            case 'futures':
                return __('model.trading_view_ticker.type.futures');
            case 'forex':
                return __('model.trading_view_ticker.type.forex');
            case 'cfd':
                return __('model.trading_view_ticker.type.cfd');
            case 'index':
                return __('model.trading_view_ticker.type.index');
            case 'crypto':
                return __('model.trading_view_ticker.type.crypto');
            case 'bond':
                return __('model.trading_view_ticker.type.bond');
            case 'economic':
                return __('model.trading_view_ticker.type.economic');
            default:
                return __('model.trading_view_ticker.type.default');
        }
    }
}
