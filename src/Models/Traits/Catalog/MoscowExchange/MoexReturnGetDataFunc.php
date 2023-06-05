<?php
namespace App\src\Models\Traits\Catalog\MoscowExchange;

use App\src\Models\Interfaces\Catalog\DefinitionActiveConst;

use function App\Models\Traits\MoscowExchange\__;

trait MoexReturnGetDataFunc
{
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
    public function getSymbolField(): string
    {
        return 'secid';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getType() . ' ' . $this->name . ' ' . $this->secid;
    }

    /**
     * @return int
     */
    public function getType()
    {
        if(in_array($this->type, self::BOND_VALUES))
        {
            return DefinitionActiveConst::OBLIGATION;
        }

        if(in_array($this->type, self::ETF_VALUE))
        {
            return DefinitionActiveConst::ETF;
        }

        if(in_array($this->type, self::FUTURES_VALUE))
        {
            return DefinitionActiveConst::FUTURES;
        }

        if(in_array($this->type, self::CURRENCY_VALUE))
        {
            return DefinitionActiveConst::CURRENCY;
        }

        return DefinitionActiveConst::STOCK;
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
        return $this->secid;
    }

    /**
     * @return int
     */
    public function getLotSize()
    {
        return $this->lotsize ?: 1;
    }

    /**
     * @return int
     */
    public function getCouponFrequency(): int
    {
        switch ($this->couponfrequency)
        {
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
     * @return string|void
     *
     * TODO https://iss.moex.com/iss/securitytypes обновить, либо придумать как автоматически это делать
     * текущие названия нужно сохранить
     */
    public function getTypeText()
    {
        switch ($this->type)
        {
            case 'common_share':
                return __('model.moscow_exchange_stock.type_text.common_share');
            case 'preferred_share':
                return __('model.moscow_exchange_stock.type_text.preferred_share');
            case 'depositary_receipt':
                return __('model.moscow_exchange_stock.type_text.depositary_receipt');
            case 'ofz_bond':
                return __('model.moscow_exchange_stock.type_text.ofz_bond');
            case 'cb_bond':
                return __('model.moscow_exchange_stock.type_text.cb_bond');
            case 'subfederal_bond':
                return __('model.moscow_exchange_stock.type_text.subfederal_bond');
            case 'municipal_bond':
                return __('model.moscow_exchange_stock.type_text.municipal_bond');
            case 'corporate_bond':
                return __('model.moscow_exchange_stock.type_text.corporate_bond');
            case 'exchange_bond':
                return __('model.moscow_exchange_stock.type_text.exchange_bond');
            case 'ifi_bond':
                return __('model.moscow_exchange_stock.type_text.ifi_bond');
            case 'euro_bond':
                return __('model.moscow_exchange_stock.type_text.euro_bond');
            case 'public_ppif':
                return __('model.moscow_exchange_stock.type_text.public_ppif');
            case 'interval_ppif':
                return __('model.moscow_exchange_stock.type_text.interval_ppif');
            case 'rts_index':
                return __('model.moscow_exchange_stock.type_text.rts_index');
            case 'private_ppif':
                return __('model.moscow_exchange_stock.type_text.private_ppif');
            case 'stock_mortgage':
                return __('model.moscow_exchange_stock.type_text.stock_mortgage');
            case 'etf_ppif':
                return __('model.moscow_exchange_stock.type_text.etf_ppif');
            case 'stock_index':
                return __('model.moscow_exchange_stock.type_text.stock_index');
            case 'exchange_ppif':
                return __('model.moscow_exchange_stock.type_text.exchange_ppif');
            case 'stock_deposit':
                return __('model.moscow_exchange_stock.type_text.stock_deposit');
            case 'non_exchange_bond':
                return __('model.moscow_exchange_stock.type_text.non_exchange_bond');
            case 'state_bond':
                return __('model.moscow_exchange_stock.type_text.state_bond');
            case 'currency_index':
            case 'currency_fixing':
                return __('model.moscow_exchange_stock.type_text.currency_index_or_fixing');
            case 'currency':
                return __('model.moscow_exchange_stock.type_text.currency');
            case 'currency_basket':
                return __('model.moscow_exchange_stock.type_text.currency_basket');
            case 'gold_metal':
                return __('model.moscow_exchange_stock.type_text.gold_metal');
            case 'silver_metal':
                return __('model.moscow_exchange_stock.type_text.silver_metal');
            case 'currency_futures':
                return __('model.moscow_exchange_stock.type_text.currency_futures');
            case 'commodity_futures':
                return __('model.moscow_exchange_stock.type_text.commodity_futures');
            case 'currency_wap':
                return __('model.moscow_exchange_stock.type_text.currency_wap');
            case 'futures':
                return __('model.moscow_exchange_stock.type_text.futures');
            case 'option':
                return __('model.moscow_exchange_stock.type_text.option');
            case 'agro_sugar':
                return __('model.moscow_exchange_stock.type_text.agro_sugar');
        }
    }
}
