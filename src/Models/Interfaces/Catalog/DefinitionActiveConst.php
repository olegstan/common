<?php

namespace Common\Models\Interfaces\Catalog;

/**
 * Interface DefinitionActiveConst
 * @package Models\Actives\Interfaces
 */
interface DefinitionActiveConst
{
    const CB = 1;
    const YAHOO_QUOTES = 2;
    const MOSCOW_EXCHANGE_QUOTES = 3;
    const CUSTOM = 4;
    const CBONDS = 5;

    const COMMODITY = 3101;
    const CURRENCY = 3102;
    const CRYPTO = 3103;
    const ETF = 3104;
    const PIF = 3105;
    const HEDGE_FUND = 3106;
    const BPIF = 3107;
    const PRECIOUS_METAL = 3108;

    const STOCK = 3201;
    const OBLIGATION = 3202;
    const BOND = 3202;
    const STRUCTURE_PRODUCT = 3203;
    const EXCHANGE_NOTE = 3205;
    const OBLIGATION_NOTE = 3206;
    const STRATEGY_DU = 3207;
    const OPTION = 3208;
    const FUTURES = 3209;

    /**
     * period types
     */
    const DAILY = 1;
    const WEEKLY = 2;
    const MONTHLY = 3;
    const QUARTER = 4;
    const HALFYEAR = 5;
    const YEARLY = 6;
    const CUSTOM_PERIOD_WEEK = 7;
    const CUSTOM_PERIOD_MONTH = 8;
    const CONDITION = 9;

    /**
     * action_id
     */
    const BUY = 1;
    const GET = 2;

    /**
     * start group types
     */
    const STOCK_GROUP_TYPE = 1001;
    const OBLIGATION_GROUP_TYPE = 1002;
    const METAL_GROUP_TYPE = 1003;
    const PROPERTY_GROUP_TYPE = 1004;
    const ALTERNATIVE_GROUP_TYPE = 1005;
    const DIRECT_GROUP_TYPE = 1006;
    const INSTRUMENT_CASH_FLOW_GROUP_TYPE = 1007;
    const CUSTOM_GROUP_TYPE = 2001;

}