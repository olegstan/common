<?php

namespace Common\Models\Interfaces;

use Common\Models\Interfaces\Catalog\DefinitionActiveConst;

interface RelationTextInterface
{
    /**
     * Все типы брокеров
     */
    public const BROKERS = [
        self::ATON_OPERATION,
        self::ATON_COMMISSION,
        self::BCS_OPERATION,
        self::BCS_COMMISSION,
        self::TINKOFF_OPERATION,
    ];

    /**
     * Только операции брокеров
     */
    public const BROKER_OPERATIONS = [
        self::ATON_OPERATION,
        self::BCS_OPERATION,
        self::TINKOFF_OPERATION,
    ];

    /**
     * Только комиссии брокеров
     */
    public const BROKER_COMMISSION = [
        self::ATON_COMMISSION,
        self::BCS_COMMISSION,
    ];

    /**
     * Все записи которые мы получаем по API
     */
    public const API_TRANSACTIONS = [
        self::ATON_OPERATION,
        self::ATON_COMMISSION,
        self::BCS_OPERATION,
        self::BCS_COMMISSION,
        self::TINKOFF_OPERATION,
        self::ZENMONEY_TRANSACTION,
        self::ZENMONEY_ACCOUNT,
    ];

    /**
     * Названия каталогов
     */
    public const CATALOGS = [
        self::CB_CATALOG,
        self::YAHOO_CATALOG,
        self::MOEX_CATALOG,
        self::CUSTOM_CATALOG,
        self::CBONDS_CATALOG,
    ];

    public const ZENMONEYS = [
        self::ZENMONEY_TRANSACTION,
        self::ZENMONEY_ACCOUNT,
    ];

    public const ATONS = [
        self::ATON_OPERATION,
        self::ATON_COMMISSION,
    ];

    public const BCSS = [
        self::BCS_OPERATION,
        self::BCS_COMMISSION,
    ];

    public const ACTIVE = 'active';
    public const ACTIVE_PAYMENT = 'active.payment';
    public const ACTIVE_INVEST = 'active.invest';
    public const ACTIVE_SELL = 'active.sell';
    public const ACTIVE_TRADE = 'active.trade';
    public const ACTIVE_COUPON = 'active.coupon';
    public const ACTIVE_TRADE_COMMISSION = 'active.trade.commission';
    public const ACTIVE_DIVIDEND = 'active.dividend';
    public const ACTIVE_GROUP = 'active.group';
    public const ACTIVE_BASE_GROUP = 'active.base.group';
    public const ACTIVE_GOAL = 'active.goal';
    public const ACTIVE_GOAL_ITEM = 'active.goal.item';
    public const ACTIVE_ACTION = 'active.action';
    public const ACTIVE_VALUATION = 'active.valuation';
    public const ACTIVE_USER_VALUATION = 'active.user.valuation';
    public const CREDITLOG = 'creditlog';
    public const ATON_OPERATION = 'aton.operation';
    public const ATON_COMMISSION = 'aton.commission';
    public const BCS_OPERATION = 'bcs.operation';
    public const BCS_COMMISSION = 'bcs.commission';
    public const TINKOFF_OPERATION = 'tinkoff.operation';
    public const CBOND_COUPON = 'cbond.coupon';
    public const MOEX_COUPON = 'moex.coupon';
    public const MOEX_DIVIDEND = 'moex.dividend';
    public const TRANSFER = 'transfer';
    public const TRANSFER_COMMISSION = 'transfer.commission';
    public const ACCOUNT = 'account';
    public const ACCOUNT_CURRENCY = 'account.currency';
    public const ACCOUNT_COMMISSION = 'account.commission';
    public const ZENMONEY_TRANSACTION = 'zenmoney.transaction';
    public const ZENMONEY_ACCOUNT = 'zenmoney.account';
    public const CB_CATALOG = DefinitionActiveConst::CB_CATALOG;
    public const YAHOO_CATALOG = DefinitionActiveConst::YAHOO_CATALOG;
    public const MOEX_CATALOG = DefinitionActiveConst::MOEX_CATALOG;
    public const CUSTOM_CATALOG = DefinitionActiveConst::CUSTOM_CATALOG;
    public const CBONDS_CATALOG = DefinitionActiveConst::CBONDS_CATALOG;
}