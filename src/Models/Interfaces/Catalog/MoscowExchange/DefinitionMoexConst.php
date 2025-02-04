<?php

namespace Common\Models\Interfaces\Catalog\MoscowExchange;

interface DefinitionMoexConst
{
    public const TYPE_FIELD = 'moscow_exchange_stocks.type';

    public const STOCK_VALUES = [
        'common_share',
        'preferred_share'
    ];

    public const CURRENCY_VALUE = [
        'currency',
    ];

    public const METAL_VALUE = [
        'silver_metal',
        'gold_metal'
    ];

    public const FUTURES_VALUE = [
        'futures',
        'currency_futures',
        'commodity_futures'
    ];

    public const ETF_VALUE = [
        'etf_ppif'
    ];

    public const BOND_VALUES = [
        'cb_bond',
        'subfederal_bond',
        'municipal_bond',
        'ifi_bond',
        'euro_bond',
        'state_bond',
        'exchange_bond',
        'corporate_bond',
        'ofz_bond',
        'non_exchange_bond',
    ];

    public const PIF_VALUES = [
        'public_ppif',
        'private_ppif',
        'exchange_ppif',
    ];
}