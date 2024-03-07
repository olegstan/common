<?php

namespace Common\Models\Interfaces\Catalog\TradingView;


interface DefinitionTradingViewConst
{
    public const TYPE_FIELD = ['type'];
    public const STOCK_VALUES = ['stock'];
    public const BOND_VALUES = ['bond'];
    public const CURRENCY_VALUE = ['forex'];
    public const FUTURES_VALUE = ['futures'];

    public const NANOCAP = 1;
    public const MICROCAP = 2;
    public const SMALLCAP = 3;
    public const MIDCAP = 4;
    public const LARGECAP = 5;
    public const MEGACAP = 6;

    public const TYPECAPS = [
        self::NANOCAP,
        self::MICROCAP,
        self::SMALLCAP,
        self::MIDCAP,
        self::LARGECAP,
        self::MEGACAP,
    ];

    public const NANOCAP_VALUE = 50000000;
    public const MICROCAP_VALUE = 250000000;
    public const SMALLCAP_VALUE = 2000000000;
    public const MIDCAP_VALUE = 10000000000;
    public const MEGACAP_VALUE = 200000000000;

    public const TYPECAPS_VALUE = [
        self::NANOCAP_VALUE,
        self::MICROCAP_VALUE,
        self::SMALLCAP_VALUE,
        self::MIDCAP_VALUE,
        self::MEGACAP_VALUE,
    ];
}
