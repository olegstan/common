<?php

namespace Common\Models\Users\Plan;

use App\Traits\Models\RemoveActives\RemoveActiveByUserId;
use Common\Models\BaseModel;

/**
 * Class UserUpdatePlan
 * @package Common\Models\Users\Plan
 */
class UserUpdatePlan extends BaseModel
{

    /**
     * @var string
     */
    public $table = 'user_update_plans';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'data',
        'type_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'data' => 'string',
        'type_id' => 'integer',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
