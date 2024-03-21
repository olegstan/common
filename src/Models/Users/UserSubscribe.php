<?php

namespace Common\Models\Users;

use Common\Models\BaseModel;

/**
 * Class UserSubscribe
 * @package App\Models
 */
class UserSubscribe extends BaseModel
{

    /**
     * @var string
     */
    public $table = 'user_subscribes';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subscribe_id',
        'order_id',
        'start_at',
        'end_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['start_at', 'end_at'];
}
