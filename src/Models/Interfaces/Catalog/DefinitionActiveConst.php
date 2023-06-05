<?php

namespace App\src\Models\Interfaces\Catalog;

/**
 * Interface DefinitionActiveConst
 * @package App\Models\Actives\Interfaces
 */
interface DefinitionActiveConst
{
    public const CB = 1;
    public const YAHOO_QUOTES = 2;
    public const MOSCOW_EXCHANGE_QUOTES = 3;
    public const CUSTOM = 4;
    public const CBONDS = 5;

    public const COMMODITY = 3101;
    public const CURRENCY = 3102;
    public const CRYPTO = 3103;
    public const ETF = 3104;
    public const PIF = 3105;
    public const HEDGE_FUND = 3106;
    public const BPIF = 3107;
    public const PRECIOUS_METAL = 3108;

    public const STOCK = 3201;
    public const OBLIGATION = 3202;
    public const BOND = 3202;
    public const STRUCTURE_PRODUCT = 3203;
    public const EXCHANGE_NOTE = 3205;
    public const OBLIGATION_NOTE = 3206;
    public const STRATEGY_DU = 3207;
    public const OPTION = 3208;
    public const FUTURES = 3209;

    /**
     * period types
     */
    public const DAILY = 1;
    public const WEEKLY = 2;
    public const MONTHLY = 3;
    public const QUARTER = 4;
    public const HALFYEAR = 5;
    public const YEARLY = 6;
    public const CUSTOM_PERIOD_WEEK = 7;
    public const CUSTOM_PERIOD_MONTH = 8;
    public const CONDITION = 9;

    /**
     * action_id
     */
    const BUY = 1;
    const GET = 2;

    /**
     * start group types
     */
    public const STOCK_GROUP_TYPE = 1001;
    public const OBLIGATION_GROUP_TYPE = 1002;
    public const METAL_GROUP_TYPE = 1003;
    public const PROPERTY_GROUP_TYPE = 1004;
    public const ALTERNATIVE_GROUP_TYPE = 1005;
    public const DIRECT_GROUP_TYPE = 1006;
    public const INSTRUMENT_CASH_FLOW_GROUP_TYPE = 1007;
    public const CUSTOM_GROUP_TYPE = 2001;

}