<?php

namespace Common\Models\Interfaces\Catalog\Cbond;

interface DefinitionCbondConst
{
    public const TYPE_FIELD = 'cbond_stocks.type';

    public const STOCK_VALUES = [
        'common_share',
        'preferred_share'
    ];

    public const CURRENCY_VALUE = [
        'currency',
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
        'exchange_ppif',
    ];
}
