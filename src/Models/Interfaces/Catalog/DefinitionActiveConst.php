<?php

namespace Common\Models\Interfaces\Catalog;

use Common\Models\Catalog\Cbond\CbondStock;
use Common\Models\Catalog\Currency\CbCurrency;
use Common\Models\Catalog\Custom\CustomStock;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeStock;
use Common\Models\Catalog\Yahoo\YahooStock;

interface DefinitionActiveConst
{
    /**
     * resources
     */
    public const CB = 1;
    public const YAHOO_QUOTES = 2;
    public const MOSCOW_EXCHANGE_QUOTES = 3;
    public const CUSTOM = 4;
    public const CBONDS = 5;

    /**
     * catalogs
     */
    public const CB_CATALOG = 'catalog.1';
    public const YAHOO_CATALOG = 'catalog.2';
    public const MOEX_CATALOG = 'catalog.3';
    public const CUSTOM_CATALOG = 'catalog.4';
    public const CBONDS_CATALOG = 'catalog.5';

    /**
     * periodic
     */
    public const ONCE = 1;
    public const PERIOD = 2;

    /**
     * типы в jobs
     */
    public const TINKOFF = 1;
    public const ZENMONEY = 2;
    public const GOAL = 3;
    public const STRATEGY = 4;
    public const GOAL_RECALC = 5;
    public const WAZZUP = 6;
    public const MAIL = 9;
    public const PLAN = 10;
    public const BCS_FILE_PARSE = 12;
    public const ACTIVE_SET_PRICE = 13;
    public const CATALOG_COURSE = 14;
    public const IRR = 15;
    public const ALL_ACTIVES = 16;
    public const REFRESH_API = 17;
    public const ZENMONEY_RELATION_BROKER_API = 18;
    public const STRATEGY_CACHE = 25;
    public const TINKOFF_ORDER = 26;
    public const RECALC_BALANCE = 27;
    public const MOEX_HISTORY = 28;
    public const YAHOO_HISTORY = 29;
    public const CBOND_HISTORY = 30;
    public const CB_HISTORY = 31;
    public const MOEX_PROFITABILITY = 32;
    public const TV_TICKERS = 33;
    public const YAHOO_DATA = 34;
    public const CBOND_PROFITABILITY = 35;
    public const CBOND_COURSE = 36;
    public const CBOND_SAVE = 37;
    public const FINEX_HISTORY = 37;
    public const MOEX_COURSE = 38;
    public const YAHOO_COURSE = 39;


    public const ATON_CRM_PARSE = 11;
    public const ATON_FILE_UPLOADER = 7;
    public const ATON_FILE_PARSE = 8;
    public const ATON_CLIENT_CREATE = 19;
    public const ATON_FILE_VALUATION = 20;
    public const ATON_FILE_SPLIT = 21;
    public const ATON_CHECK_VALUATION = 22;
    public const ATON_FILE_HISTORY = 23;
    public const ATON_CREATE_ACTION = 24;



    /**
     * start types
     */
    public const SALARY = 1001;

    public const CUSTOM_SALARY = 1004;

    public const FLAT = 2001;
    public const HOUSE = 2002;
    public const LAND = 2003;

    public const CAR = 2101;
    public const MOTO = 2102;
    public const TECHNIC = 2103;

    public const JEWELRY = 2201;
    public const PERSONAL_TECHNIC = 2202;
    public const CUSTOM_PROPERTY = 2203;

    public const DEPOSIT = 3001;
    public const DEBT = 3002;

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

    public const RENT_CAR = 9001;
    public const RENT_FLAT = 9002;
    public const SPEND_LIFE = 9003;
    public const CUSTOM_OBLIGATION = 9005;
    public const CAR_CREDIT = 9006;
    public const FLAT_CREDIT = 9007;
    public const CREDIT = 9008;
    public const ALIMONY = 9009;
    public const LOAN = 9010;

    public const MONEY_ACTIVE = 10001;

    public const PRODUCTS_SPEND = 11001;
    public const CAFE_SPEND = 11002;
    public const CAR_SPEND = 11003;
    public const TRANSPORT_SPEND = 11004;
    public const MEDICINE_SPEND = 11005;
    public const BEAUTY_SPEND = 11006;
    public const CLOTHES_ADULT_SPEND = 11007;
    public const CLOTHES_CHILD_SPEND = 11008;
    public const TOYS_SPEND = 11009;
    public const VACATION_SPEND = 11010;
    public const EDUCATION_SPEND = 11011;
    public const RENT_SPEND = 11012;
    public const FUN_SPEND = 11013;
    public const PRESENTS_SPEND = 11014;
    public const SPORT_SPEND = 11015;
    public const TELECOM_SPEND = 11016;
    public const CUSTOM_SPEND = 9004;

    public const SALARY_INCOME = 12001;
    public const BONUS_INCOME = 12002;//премия
    public const RETIRE_INCOME = 12003;
    public const RENT_INCOME = 12004;
    public const RELATIVES_INCOME = 12005;
    public const PASSIVE_INCOME = 12006;
    public const AGENT_INCOME = 12007;
    public const CUSTOM_INCOME = 1003;

    //страховки
    public const FUNDED_LIFE_INSURANCE = 3003;
    public const INVESTMENT_LIFE_INSURANCE = 3204;
    public const AUTO_INSURANCE = 13001;
    public const PROPERTY_INSURANCE = 13002;
    public const HEALTH_INSURANCE = 13003;
    public const VMI_INSURANCE = 13004;
    public const TRAVEL_INSURANCE = 13005;
    public const SPORT_INSURANCE = 13006;
    public const UNIT_LINKED_INSURANCE = 13007;
    public const LOSE_JOB_INSURANCE = 13008;
    public const RESPONSIBILITY_INSURANCE = 13009;
    public const PET_INSURANCE = 13010;
    public const CUSTOM_INSURANCE = 13011;

    public const ALPHA_INS = 1001;
    public const INGOS_INS = 1002;
    public const ROSGOS_INS = 1003;
    public const VSK_INS = 1004;
    public const RESO_INS = 1005;
    public const SOGAZ_INS = 1006;
    public const MAKS_INS = 1007;
    public const RENESANS_INS = 1008;
    public const CAPITAL_LIFE_INS = 1009;
    public const SOGLASIE_INS = 1010;
    public const SBER_INS = 1011;
    public const TINKOFF_INS = 1012;
    public const CUSTOM_INS = 2001;

    public const GROUP_QUERY_CATALOG = [
        DefinitionActiveConst::STOCK,
        DefinitionActiveConst::ETF,
        DefinitionActiveConst::OBLIGATION,
        DefinitionActiveConst::FUTURES,
        DefinitionActiveConst::PIF,
        DefinitionActiveConst::PRECIOUS_METAL,
    ];

    public const INSURANCE_COMPANY_GROUP = [
        self::ALPHA_INS,
        self::INGOS_INS,
        self::ROSGOS_INS,
        self::VSK_INS,
        self::RESO_INS,
        self::SOGAZ_INS,
        self::MAKS_INS,
        self::RENESANS_INS,
        self::CAPITAL_LIFE_INS,
        self::SOGLASIE_INS,
        self::SBER_INS,
        self::TINKOFF_INS,
    ];

    /**
     * end types
     */

    public const INCOME_GROUP = [
        self::SALARY_INCOME,
        self::BONUS_INCOME,
        self::RETIRE_INCOME,
        self::RENT_INCOME,
        self::RELATIVES_INCOME,
        self::PASSIVE_INCOME,
        self::AGENT_INCOME,
    ];

    public const INSURANCES_GROUP = [
        self::AUTO_INSURANCE,
        self::PROPERTY_INSURANCE,
        self::HEALTH_INSURANCE,
        self::VMI_INSURANCE,
        self::TRAVEL_INSURANCE,
        self::SPORT_INSURANCE,
        self::UNIT_LINKED_INSURANCE,
        self::LOSE_JOB_INSURANCE,
        self::RESPONSIBILITY_INSURANCE,
        self::PET_INSURANCE,
        self::INVESTMENT_LIFE_INSURANCE,
        self::FUNDED_LIFE_INSURANCE
    ];

    public const SALARY_GROUP = [
        self::SALARY
    ];

    public const SPENDING_GROUP = [
        self::PRODUCTS_SPEND,
        self::CAFE_SPEND,
        self::CAR_SPEND,
        self::TRANSPORT_SPEND,
        self::MEDICINE_SPEND,
        self::BEAUTY_SPEND,
        self::CLOTHES_ADULT_SPEND,
        self::CLOTHES_CHILD_SPEND,
        self::TOYS_SPEND,
        self::VACATION_SPEND,
        self::EDUCATION_SPEND,
        self::RENT_SPEND,
        self::FUN_SPEND,
        self::PRESENTS_SPEND,
        self::SPORT_SPEND,
        self::TELECOM_SPEND,
    ];

    public const SPENDING_GROUP_ICON = [
        self::PRODUCTS_SPEND => 'food.svg',
        self::CAFE_SPEND => 'cafe.svg',
        self::CAR_SPEND => 'car.svg',
        self::TRANSPORT_SPEND => 'bus.svg',
        self::MEDICINE_SPEND => 'health.svg',
        self::BEAUTY_SPEND => 'beauty.svg',
        self::CLOTHES_ADULT_SPEND => 'dress_adult.svg',
        self::CLOTHES_CHILD_SPEND => 'dress_child.svg',
        self::TOYS_SPEND => 'toy.svg',
        self::VACATION_SPEND => 'travel.svg',
        self::EDUCATION_SPEND => 'education.svg',
        self::RENT_SPEND => 'house.svg',
        self::FUN_SPEND => 'game.svg',
        self::PRESENTS_SPEND => 'gift.svg',
        self::SPORT_SPEND => 'sport.svg',
        self::TELECOM_SPEND => 'phone.svg',
    ];

    public const INCOME_GROUP_ICON = [
        self::SALARY_INCOME => 'salary.svg',
        self::BONUS_INCOME => 'award.svg',
        self::RETIRE_INCOME => 'pension.svg',
        self::RENT_INCOME => 'renta.svg',
        self::RELATIVES_INCOME => 'relatives_help.svg',
        self::PASSIVE_INCOME => 'business_income.svg',
        self::AGENT_INCOME => 'agent.svg',
    ];

    public const SPEND_OBLIGATION_GROUP = [
        self::RENT_CAR,
        self::RENT_FLAT,
        self::SPEND_LIFE
    ];

    public const CREDIT_OBLIGATION_GROUP = [
        self::CAR_CREDIT,
        self::FLAT_CREDIT,
        self::CREDIT,
        self::ALIMONY,
        self::LOAN,
    ];

    public const PROPERTY_GROUP = [
        self::FLAT,
        self::HOUSE,
        self::LAND,
        self::CAR,
//        self::MOTO,
//        self::TECHNIC,
//        self::JEWELRY,
//        self::PERSONAL_TECHNIC,
    ];

    public const INVEST_GROUP = [
        self::STOCK,
        self::OBLIGATION,
        self::PIF,
        self::BPIF,
        self::ETF,
        self::STRUCTURE_PRODUCT,
        self::EXCHANGE_NOTE,
        self::OBLIGATION_NOTE,
        self::CURRENCY,
        self::PRECIOUS_METAL,
        self::STRATEGY_DU,
        self::CRYPTO,
        self::COMMODITY,
        self::FUTURES,
        self::OPTION,
        self::HEDGE_FUND,
        self::DEPOSIT,
        self::DEBT,
    ];

    public const COUPON_GROUP = [
        self::OBLIGATION,
        self::STRUCTURE_PRODUCT,
        self::EXCHANGE_NOTE,
        self::OBLIGATION_NOTE,
    ];

    public const PACKAGE_GROUP = [
        self::STOCK,
        self::OBLIGATION,
        self::PIF,
        self::BPIF,
        self::ETF,
        self::STRUCTURE_PRODUCT,
        self::EXCHANGE_NOTE,
        self::OBLIGATION_NOTE,
        self::CURRENCY,
        self::PRECIOUS_METAL,
        self::STRATEGY_DU,
        self::CRYPTO,
        self::COMMODITY,
        self::FUTURES,
        self::OPTION,
        self::HEDGE_FUND,
    ];

    public const TICKER_GROUP = [
        self::CURRENCY,
        self::STOCK,
        self::ETF,
        self::OBLIGATION,
        self::FUTURES
    ];

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

    public const PERIOD_GROUP = [
//        self::DAILY,
//        self::WEEKLY,
        self::MONTHLY,
        self::QUARTER,
        self::HALFYEAR,
        self::YEARLY,
//        self::CUSTOM_PERIOD,
//        self::CONDITION
    ];

    /**
     * action strategy types
     */
    public const PLUS = 1;
    public const MINUS = 2;
    public const EQUAL = 3;

    /**
     *
     */
    public const MONEY = 1;
    public const INVEST = 2;
    public const OWN = 3;

    /**
     * action_id
     */
    public const BUY = 1;
    public const GET = 2;

    /**
     * percent debt types
     */
    public const SIMPLE = 1;
    public const DIFFERENTIAL = 2;

    public const DEPOSIT_PERCENT_GROUP = [
        self::SIMPLE,
        self::DIFFERENTIAL
    ];

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

    /**
     * end group types
     */
    public const GROUP_TYPES_GROUP = [
        self::STOCK_GROUP_TYPE,
        self::OBLIGATION_GROUP_TYPE,
        self::METAL_GROUP_TYPE,
        self::PROPERTY_GROUP_TYPE,
        self::ALTERNATIVE_GROUP_TYPE,
        self::DIRECT_GROUP_TYPE,
        self::INSTRUMENT_CASH_FLOW_GROUP_TYPE
    ];

    public const GROUP_TYPES_GROUP_ICON = [
        self::STOCK_GROUP_TYPE => 'investment.svg',
        self::OBLIGATION_GROUP_TYPE => 'bond.svg',
        self::METAL_GROUP_TYPE => 'metal.svg',
        self::PROPERTY_GROUP_TYPE => 'house.svg',
        self::ALTERNATIVE_GROUP_TYPE => 'alt_investment.svg',
        self::DIRECT_GROUP_TYPE => 'money_tool.svg',
        self::INSTRUMENT_CASH_FLOW_GROUP_TYPE => 'money_tool.svg',
    ];

    /**
     * class types
     */
    public const CUSTOM_CLASS_TYPE = 2001;

    public const GROUP_CLASS_TYPES = [
        self::CUSTOM_CLASS_TYPE
    ];

    /**
     * All catalog classes
     */
    public const ALL_CATALOG_CLASSES = [
      MoscowExchangeStock::class,
      CbondStock::class,
      YahooStock::class,
      CbCurrency::class,
      CustomStock::class,
    ];
}