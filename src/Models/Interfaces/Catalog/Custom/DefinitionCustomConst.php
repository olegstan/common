<?php

namespace Common\Models\Interfaces\Catalog\Custom;


use Common\Models\Interfaces\Catalog\DefinitionActiveConst;

interface DefinitionCustomConst
{
    public const TYPE_FIELD = 'custom_stocks.type_id';
    public const STOCK_VALUES = [
        DefinitionActiveConst::STOCK,
    ];

    public const CURRENCY_VALUE = [
        DefinitionActiveConst::CURRENCY,
    ];

    public const FUTURES_VALUE = [
        DefinitionActiveConst::FUTURES,
    ];

    public const ETF_VALUE = [
        DefinitionActiveConst::ETF,
    ];

    public const BOND_VALUES = [
        DefinitionActiveConst::BOND,
        DefinitionActiveConst::OBLIGATION,
    ];

    public const PIF_VALUES = [
        DefinitionActiveConst::PIF,
        DefinitionActiveConst::BPIF,
    ];
}
