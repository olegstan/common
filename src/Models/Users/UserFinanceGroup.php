<?php

namespace Common\Models\Users;

use Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $user_id
 * @property int $union_user_id
 * @property int $type_id
 */
class UserFinanceGroup extends BaseModel
{
    const FINANCE = 1001;

    /**
     * Название таблицы
     *
     * @var string
     */
    public $table = 'user_finance_groups';

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
    public function unionUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'union_user_id');
    }
}