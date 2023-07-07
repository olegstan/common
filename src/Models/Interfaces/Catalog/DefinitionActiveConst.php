<?php

namespace Common\Models\Interfaces\Catalog;

use App\Models\Actives\Invests\Quotes\Pif;
use App\Models\Actives\Invests\QuotesAndPay\Futures;
use App\Models\Actives\Invests\QuotesAndPay\Obligation;
use App\Models\Actives\Invests\QuotesAndPay\Stock;

interface DefinitionActiveConst
{
    /**
     * resources
     */
    const CB = 1;
    const YAHOO_QUOTES = 2;
    const MOSCOW_EXCHANGE_QUOTES = 3;
    const CUSTOM = 4;
    const CBONDS = 5;

    /**
     * periodic
     */
    const ONCE = 1;
    const PERIOD = 2;

    /**
     * типы в jobs
     */
    public const TINKOFF = 1;
    public const ZENMONEY = 2;
    public const GOAL = 3;
    public const STRATEGY = 4;
    public const GOAL_RECALC = 5;
    public const WAZZUP = 6;
    public const ATON_FILE_UPLOADER = 7;
    public const ATON_FILE_PARSE = 8;
    public const MAIL = 9;
    public const PLAN = 10;
    public const ATON_CRM_PARSE = 11;
    public const BCS_FILE_PARSE = 12;

    /**
     * start types
     */
    const SALARY = 1001;

    const CUSTOM_SALARY = 1004;

    const FLAT = 2001;
    const HOUSE = 2002;
    const LAND = 2003;

    const CAR = 2101;
    const MOTO = 2102;
    const TECHNIC = 2103;

    const JEWELRY = 2201;
    const PERSONAL_TECHNIC = 2202;
    const CUSTOM_PROPERTY = 2203;

    const DEPOSIT = 3001;
    const DEBT = 3002;

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

    const RENT_CAR = 9001;
    const RENT_FLAT = 9002;
    const SPEND_LIFE = 9003;
    const CUSTOM_OBLIGATION = 9005;
    const CAR_CREDIT = 9006;
    const FLAT_CREDIT = 9007;
    const CREDIT = 9008;

    const MONEY_ACTIVE = 10001;

    const PRODUCTS_SPEND = 11001;
    const CAFE_SPEND = 11002;
    const CAR_SPEND = 11003;
    const TRANSPORT_SPEND = 11004;
    const MEDICINE_SPEND = 11005;
    const BEAUTY_SPEND = 11006;
    const CLOTHES_ADULT_SPEND = 11007;
    const CLOTHES_CHILD_SPEND = 11008;
    const TOYS_SPEND = 11009;
    const VACATION_SPEND = 11010;
    const EDUCATION_SPEND = 11011;
    const RENT_SPEND = 11012;
    const FUN_SPEND = 11013;
    const PRESENTS_SPEND = 11014;
    const SPORT_SPEND = 11015;
    const TELECOM_SPEND = 11016;
    const CUSTOM_SPEND = 9004;

    const SALARY_INCOME = 12001;
    const BONUS_INCOME = 12002;//премия
    const RETIRE_INCOME = 12003;
    const RENT_INCOME = 12004;
    const RELATIVES_INCOME = 12005;
    const PASSIVE_INCOME = 12006;
    const AGENT_INCOME = 12007;
    const CUSTOM_INCOME = 1003;

    //страховки
    const FUNDED_LIFE_INSURANCE = 3003;
    const INVESTMENT_LIFE_INSURANCE = 3204;
    const CAR_INSURANCE = 13001;
    const PROPERTY_INSURANCE = 13002;
    const HEALTH_INSURANCE = 13003;
    const VMI_INSURANCE = 13004;
    const TRAVEL_INSURANCE = 13005;
    const SPORT_INSURANCE = 13006;
    const UNIT_LINKED_INSURANCE = 13007;
    const LOSE_JOB_INSURANCE = 13008;
    const RESPONSIBILITY_INSURANCE = 13009;
    const PET_INSURANCE = 13010;
    const CUSTOM_INSURANCE = 13011;

    const ALPHA_INS = 1001;
    const INGOS_INS = 1002;
    const ROSGOS_INS = 1003;
    const VSK_INS = 1004;
    const RESO_INS = 1005;
    const SOGAZ_INS = 1006;
    const MAKS_INS = 1007;
    const RENESANS_INS = 1008;
    const CAPITAL_LIFE_INS = 1009;
    const SOGLASIE_INS = 1010;
    const SBER_INS = 1011;
    const TINKOFF_INS = 1012;
    const CUSTOM_INS = 2001;

    const INSURANCE_COMPANY_GROUP = [
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

    const INCOME_GROUP = [
        self::SALARY_INCOME,
        self::BONUS_INCOME,
        self::RETIRE_INCOME,
        self::RENT_INCOME,
        self::RELATIVES_INCOME,
        self::PASSIVE_INCOME,
        self::AGENT_INCOME,
    ];

    const INSURANCES_GROUP = [
        self::CAR_INSURANCE,
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

    const SALARY_GROUP = [
        self::SALARY
    ];

    const SPENDING_GROUP = [
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

    const SPENDING_GROUP_ICON = [
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

    const INCOME_GROUP_ICON = [
        self::SALARY_INCOME => 'salary.svg',
        self::BONUS_INCOME => 'award.svg',
        self::RETIRE_INCOME => 'pension.svg',
        self::RENT_INCOME => 'renta.svg',
        self::RELATIVES_INCOME => 'relatives_help.svg',
        self::PASSIVE_INCOME => 'business_income.svg',
        self::AGENT_INCOME => 'agent.svg',
    ];

    const SPEND_OBLIGATION_GROUP = [
        self::RENT_CAR,
        self::RENT_FLAT,
        self::SPEND_LIFE
    ];

    const CREDIT_OBLIGATION_GROUP = [
        self::CAR_CREDIT,
        self::FLAT_CREDIT,
        self::CREDIT
    ];

    const PROPERTY_GROUP = [
        self::FLAT,
        self::HOUSE,
        self::LAND,
        self::CAR,
//        self::MOTO,
//        self::TECHNIC,
//        self::JEWELRY,
//        self::PERSONAL_TECHNIC,
    ];

    const INVEST_GROUP = [
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

    const PACKAGE_GROUP = [
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

    const TICKER_GROUP = [
        self::CURRENCY,
        self::STOCK,
        self::ETF,
        self::OBLIGATION,
        self::FUTURES
    ];

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

    const PERIOD_GROUP = [
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
    const PLUS = 1;
    const MINUS = 2;
    const EQUAL = 3;

    /**
     *
     */
    const MONEY = 1;
    const INVEST = 2;
    const OWN = 3;

    /**
     * action_id
     */
    const BUY = 1;
    const GET = 2;

    /**
     * percent debt types
     */
    const SIMPLE = 1;
    const DIFFERENTIAL = 2;

    const DEPOSIT_PERCENT_GROUP = [
        self::SIMPLE,
        self::DIFFERENTIAL
    ];

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

    public const CATALOG_CLASSES = [
        'stock' => Stock::class,
        'obligation' => Obligation::class,
        'currency' => Currency::class,
        'etf' => Etf::class,
        'pif' => Pif::class,
        'futures' => Futures::class,
    ];

    /**
     * end group types
     */
    const GROUP_TYPES_GROUP = [
        self::STOCK_GROUP_TYPE,
        self::OBLIGATION_GROUP_TYPE,
        self::METAL_GROUP_TYPE,
        self::PROPERTY_GROUP_TYPE,
        self::ALTERNATIVE_GROUP_TYPE,
        self::DIRECT_GROUP_TYPE,
        self::INSTRUMENT_CASH_FLOW_GROUP_TYPE
    ];

    const GROUP_TYPES_GROUP_ICON = [
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
    const CUSTOM_CLASS_TYPE = 2001;

    const GROUP_CLASS_TYPES = [
        self::CUSTOM_CLASS_TYPE
    ];

}