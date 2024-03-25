<?php

namespace Common\Models\Users\CreditLog;

use Common\Models\BaseModel;
use Common\Models\Users\User;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property $user_id
 * @property $comment
 * @property $credits_before
 * @property $credits_after
 * @property $points_before
 * @property $points_after
 * @property $sum
 * @property $point_sum
 * @property $type_id
 * @property $created_at
 */
class UserCreditLog extends BaseModel
{
    /**
     * @var string
     */
    public $table = 'user_credit_logs';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'comment',
        'credits_before',
        'credits_after',
        'points_before',
        'points_after',
        'sum',
        'point_sum',
        'created_at',
        'type_id',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'user_id' => 'integer',
        'comment' => 'string',
        'credits_before' => 'double',
        'credits_after' => 'double',
        'points_before' => 'double',
        'points_after' => 'double',
        'sum' => 'double',
        'point_sum' => 'double',
        'created_at' => 'datetime',
        'type_id' => 'integer',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    public const REFILL = 1;
    public const WITHDRAWAL = 2;

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
