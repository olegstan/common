<?php

namespace Common\Models\Traits\Catalog\TradingView;

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
     * @return string
     */
    public function getTypeText(): string
    {
        switch ($this->type)
        {
            case 'futures':
                return 'Фьючерс';
//                return __('model.trading_view_ticker.type.futures');
            case 'forex':
                return 'Валюта';
//                return __('model.trading_view_ticker.type.forex');
            case 'cfd':
                return 'cfd';
//                return __('model.trading_view_ticker.type.cfd');
            case 'index':
                return 'Индекс';
//                return __('model.trading_view_ticker.type.index');
            case 'crypto':
                return 'Криптовалюта';
//                return __('model.trading_view_ticker.type.crypto');
            case 'bond':
                return 'Облигация';
//                return __('model.trading_view_ticker.type.bond');
            case 'economic':
                return 'Экономика';
//                return __('model.trading_view_ticker.type.economic');
            default:
                return 'Акции';
//                return __('model.trading_view_ticker.type.default');
        }
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon ? config('app.url') . '/images/icons/' . $this->icon . '.svg' : config('app.url') . '/images/icons/default.svg';
    }
}
