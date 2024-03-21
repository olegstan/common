<?php

namespace Common\Models\Users;

use Common\Models\BaseModel;

/**
 * Class UserConfig
 * @package App\Models
 */
class UserConfig extends BaseModel
{

    /**
     * 1001:Не удалять, 1002:1 день', 1003:1 неделя, 1004:2 недели, 1005:1 месяц
     */
    const C_NOTIFICATION_DELETE_AFTER_VALUE = 'NOTIFICATION_DELETE_AFTER_VALUE';
    /**
     * User config (set week holidays)
     */
    const C_WEEK_HOLIDAYS = 'WEEK_HOLIDAYS';

    /**
     * Возможное количество переносов (с 1 до 365), 0: без лимитов
     */
    const C_APPLICATION_MAX_DELAY_DAYS = 'APPLICATION_MAX_DELAY_DAYS';

    // CALENDAR CONFIG
    /**
     * Интервал сетки в минутах
     */
    const C_CALENDAR_SLOT_DURATION = 'CALENDAR_SLOT_DURATION';
    /**
     * Время начала рабочего дня
     */
    const C_CALENDAR_SLOT_MIN_TIME = 'CALENDAR_SLOT_MIN_TIME';
    /**
     * Время завершения рабочего дня
     */
    const C_CALENDAR_SLOT_MAX_TIME = 'CALENDAR_SLOT_MAX_TIME';

    //ATON CONFIG
    const C_ATON_LOGIN = 'ATON_LOGIN';
    const C_ATON_PASS = 'ATON_PASS';
    const C_ATON_GROUP = 'ATON_GROUP';
    const C_ATON_ACCOUNT = 'ATON_ACCOUNT';
    const C_ATON_PATH_TO_2FA = 'ATON_PATH_TO_2FA';

    const  UserDefaultConfigConstants = [
        self::C_NOTIFICATION_DELETE_AFTER_VALUE => 1001,
        self::C_APPLICATION_MAX_DELAY_DAYS => 0,
        self::C_WEEK_HOLIDAYS => [
            1 => false,
            2 => false,
            3 => false,
            4 => false,
            5 => false,
            6 => true,
            7 => true
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

    /**
     * @var string
     */
    public $table = 'user_configs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'key',
        'value',
        'type',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'key' => 'string',
        'value' => 'string',
        'type' => 'integer',
    ];

    public $timestamps = false;

    /**
     * @return \Common\Models\Traits\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
