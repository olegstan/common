<?php

namespace Common\Models\Interfaces\Users\Crm;

interface UserConfigConstantsTrait
{
    /**
     * 1001:Не удалять, 1002:1 день', 1003:1 неделя, 1004:2 недели, 1005:1 месяц
     */
    public const C_NOTIFICATION_DELETE_AFTER_VALUE = 'NOTIFICATION_DELETE_AFTER_VALUE';
    /**
     * User config (set week holidays)
     */
    public const C_WEEK_HOLIDAYS = 'WEEK_HOLIDAYS';

    /**
     * Возможное количество переносов (с 1 до 365), 0: без лимитов
     */
    public const C_APPLICATION_MAX_DELAY_DAYS = 'APPLICATION_MAX_DELAY_DAYS';

    // CALENDAR CONFIG
    /**
     * Интервал сетки в минутах
     */
    public const C_CALENDAR_SLOT_DURATION = 'CALENDAR_SLOT_DURATION';
    /**
     * Время начала рабочего дня
     */
    public const C_CALENDAR_SLOT_MIN_TIME = 'CALENDAR_SLOT_MIN_TIME';
    /**
     * Время завершения рабочего дня
     */
    public const C_CALENDAR_SLOT_MAX_TIME = 'CALENDAR_SLOT_MAX_TIME';

    //ATON CONFIG
    public const C_ATON_LOGIN = 'ATON_LOGIN';
    public const C_ATON_PASS = 'ATON_PASS';
    public const C_ATON_GROUP = 'ATON_GROUP';
    public const C_ATON_ACCOUNT = 'ATON_ACCOUNT';
    public const C_ATON_PATH_TO_2FA = 'ATON_PATH_TO_2FA';

    public const MANAGER_PHONE_NUMBER = 'MANAGER_PHONE_NUMBER';
    public const MANAGER_PHONE_TOKEN = 'MANAGER_PHONE_TOKEN';
    public const MANAGER_PHONE_PROXY_TOKEN = 'MANAGER_PHONE_PROXY_TOKEN';

    public const HIERARCHY_NODE_POSITIONS = 'HIERARCHY_NODE_POSITIONS';

    public const C_ATON_CONFIGS = [
        'aton_login' => self::C_ATON_LOGIN,
        'aton_pass' => self::C_ATON_PASS,
        'aton_group' => self::C_ATON_GROUP,
        'aton_account' => self::C_ATON_ACCOUNT,
        'aton_path_to_2fa' => self::C_ATON_PATH_TO_2FA,
    ];

    public const UserDefaultConfigConstants = [
        self::C_NOTIFICATION_DELETE_AFTER_VALUE => 1001,
        self::C_APPLICATION_MAX_DELAY_DAYS => 0,
        self::C_WEEK_HOLIDAYS => [
            1 => false,
            2 => false,
            3 => false,
            4 => false,
            5 => false,
            6 => true,
            7 => true,
        ],
        self::C_CALENDAR_SLOT_DURATION => 30,
        self::C_CALENDAR_SLOT_MIN_TIME => '09:00',
        self::C_CALENDAR_SLOT_MAX_TIME => '18:00',
        self::C_ATON_LOGIN => null,
        self::C_ATON_ACCOUNT => null,
        self::C_ATON_PASS => null,
        self::C_ATON_GROUP => null,
        self::C_ATON_PATH_TO_2FA => null,
    ];
    
    ########## API SETTINGS ##########
    
    public const ATON_API = 'aton.api';
    public const BCS_API = 'bcs.api';
    public const TINKOFF_API = 'tinkoff.api';
    public const FREEDOM_API = 'freedom.api';

    public const ALL_BROKER_API = [
        self::ATON_API,
        self::BCS_API,
        self::TINKOFF_API,
        self::FREEDOM_API,
    ];
}