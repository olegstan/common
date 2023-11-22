<?php

namespace Common\Models\Traits\Catalog\Cbond;

use Common\Models\Interfaces\Catalog\DefinitionActiveConst;

trait CbondReturnGetDataFunc
{
    /**
     * @return string
     */
    public function getTypeText(): string
    {
        switch ($this->type) {
            case 'preferred_share':
                return 'Акции привилегированные';
//                return __('model.moscow_exchange_stock.type_text.preferred_share');
            case 'depositary_receipt':
                return 'Депозитарная расписка';
//                return __('model.moscow_exchange_stock.type_text.depositary_receipt');
            case 'ofz_bond':
                return 'ОФЗ';
//                return __('model.moscow_exchange_stock.type_text.ofz_bond');
            case 'cb_bond':
                return 'Облигация центрального банка';
//                return __('model.moscow_exchange_stock.type_text.cb_bond');
            case 'subfederal_bond':
                return 'Региональная облигация';
//                return __('model.moscow_exchange_stock.type_text.subfederal_bond');
            case 'municipal_bond':
                return 'Муниципальная облигация';
//                return __('model.moscow_exchange_stock.type_text.municipal_bond');
            case 'corporate_bond':
                return 'Корпоративная облигация';
//                return __('model.moscow_exchange_stock.type_text.corporate_bond');
            case 'exchange_bond':
                return 'Биржевая облигация';
//                return __('model.moscow_exchange_stock.type_text.exchange_bond');
            case 'ifi_bond':
                return 'Облигация МФО';
//                return __('model.moscow_exchange_stock.type_text.ifi_bond');
            case 'euro_bond':
                return 'Еврооблигации';
//                return __('model.moscow_exchange_stock.type_text.euro_bond');
            case 'public_ppif':
                return 'Пай открытого ПИФа';
//                return __('model.moscow_exchange_stock.type_text.public_ppif');
            case 'interval_ppif':
                return 'Пай интервального ПИФа';
//                return __('model.moscow_exchange_stock.type_text.interval_ppif');
            case 'rts_index':
                return 'Индекс РТС';
//                return __('model.moscow_exchange_stock.type_text.rts_index');
            case 'private_ppif':
                return 'Пай закрытого ПИФа';
//                return __('model.moscow_exchange_stock.type_text.private_ppif');
            case 'stock_mortgage':
                return 'Ипотечный сертификат';
//                return __('model.moscow_exchange_stock.type_text.stock_mortgage');
            case 'etf_ppif':
                return 'ETF';
//                return __('model.moscow_exchange_stock.type_text.etf_ppif');
            case 'stock_index':
                return 'Индекс фондового рынка';
//                return __('model.moscow_exchange_stock.type_text.stock_index');
            case 'exchange_ppif':
                return 'Пай биржевого ПИФа';
//                return __('model.moscow_exchange_stock.type_text.exchange_ppif');
            case 'stock_deposit':
                return 'Депозит с ЦК';
//                return __('model.moscow_exchange_stock.type_text.stock_deposit');
            case 'non_exchange_bond':
                return 'Коммерческая облигация';
//                return __('model.moscow_exchange_stock.type_text.non_exchange_bond');
            case 'state_bond':
                return 'Государственная облигация';
//                return __('model.moscow_exchange_stock.type_text.state_bond');
            case 'currency_index':
            case 'currency_fixing':
                return 'Валютный фиксинг';
//                return __('model.moscow_exchange_stock.type_text.currency_index_or_fixing');
            case 'currency':
                return 'Валюта';
//                return __('model.moscow_exchange_stock.type_text.currency');
            case 'currency_basket':
                return 'Бивалютная корзина';
//                return __('model.moscow_exchange_stock.type_text.currency_basket');
            case 'gold_metal':
                return 'Металл золото';
//                return __('model.moscow_exchange_stock.type_text.gold_metal');
            case 'silver_metal':
                return 'Металл серебро';
//                return __('model.moscow_exchange_stock.type_text.silver_metal');
            case 'currency_futures':
                return 'Валютный фьючерс';
//                return __('model.moscow_exchange_stock.type_text.currency_futures');
            case 'commodity_futures':
                return 'Товарный фьючерс';
//                return __('model.moscow_exchange_stock.type_text.commodity_futures');
            case 'currency_wap':
                return 'Средневзвешенный курс';
//                return __('model.moscow_exchange_stock.type_text.currency_wap');
            case 'futures':
                return 'Фьючерс';
//                return __('model.moscow_exchange_stock.type_text.futures');
            case 'option':
                return 'Опцион';
//                return __('model.moscow_exchange_stock.type_text.option');
            case 'agro_sugar':
                return 'Сахар';
//                return __('model.moscow_exchange_stock.type_text.agro_sugar');
            default:
                return 'Акции';
        }
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        if (in_array($this->type, self::BOND_VALUES)) {
            return DefinitionActiveConst::OBLIGATION;
        }

        if (in_array($this->type, self::ETF_VALUE)) {
            return DefinitionActiveConst::ETF;
        }

        if (in_array($this->type, self::FUTURES_VALUE)) {
            return DefinitionActiveConst::FUTURES;
        }

        if (in_array($this->type, self::CURRENCY_VALUE)) {
            return DefinitionActiveConst::CURRENCY;
        }

        return DefinitionActiveConst::STOCK;
    }

    /**
     * @return int
     */
    public function getCouponFrequency(): int
    {
        switch ($this->couponfrequency) {
            case 2:
                return DefinitionActiveConst::HALFYEAR;
            case 4:
                return DefinitionActiveConst::QUARTER;
            case 12:
                return DefinitionActiveConst::MONTHLY;
            default:
                return DefinitionActiveConst::YEARLY;//если из справочника ничего не возвращает, то считаем что это годовая облигация, похожая ситуация у RU000A105BL8
        }
    }

    /**
     * @return string
     */
    public function getDateField(): string
    {
        return 'tradedate';
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
        return 'secid';
    }

    /**
     * @return string
     */
    public function getIsinField(): string
    {
        return 'isin';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        $name = $this->name ?? $this->shortname;
        return $this->getType() . ' ' . $name . ' ' . $this->isin;
    }

    /**
     * @return mixed
     */
    public function getCodeCurrency()
    {
        return $this->faceunit;
    }

    /**
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->isin ?? '';
    }

    /**
     * @return int
     */
    public function getLotSize(): int
    {
        return $this->lotsize ?: 1;
    }

    /**
     * @return mixed
     */
    public function getStockName()
    {
        return $this->name ?? $this->shortname;
    }

    /**
     * @return mixed
     */
    public function getCouponPercent()
    {
        return $this->couponpercent;
    }

    /**
     * @return mixed
     */
    public function getMaturityDate()
    {
        return $this->matdate;
    }
}
