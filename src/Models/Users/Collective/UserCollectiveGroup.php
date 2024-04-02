<?php

namespace Common\Models\Users\Collective;

use Common\Models\BaseModel;
use Common\Models\Users\User;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $user_id
 * @property int $union_user_id
 * @property int $type_id
 */
class UserCollectiveGroup extends BaseModel
{
    public const TYPES = [
        self::FAMILY,
        self::EMPLOYEE,
    ];

    public const FAMILY = 1;
    public const EMPLOYEE = 2;

    /**
     * Название таблицы
     *
     * @var string
     */
    public $table = 'user_collective_groups';

    /**
     * Поля, доступные для заполнения
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'union_user_id',
        'type_id',
    ];

    /**
     * Поля, приведенные к определенным типам
     *
     * @var string[]
     */
    protected $casts = [
        'user_id' => 'integer',
        'union_user_id' => 'integer',
        'type_id' => 'integer',
    ];

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return HasOne
     */
    public function union_user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'union_user_id');
    }

    /**
     * Возвращает ID группы коллектива.
     *
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Возвращает ID пользователя.
     *
     * @return integer
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * Возвращает ID объединенного пользователя.
     *
     * @return integer
     */
    public function getUnionUserId(): int
    {
        return $this->union_user_id;
    }

    /**
     * Возвращает тип ID.
     *
     * @return integer
     */
    public function getTypeId(): int
    {
        return $this->type_id;
    }
}