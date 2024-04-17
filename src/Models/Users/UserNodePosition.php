<?php

namespace Common\Models\Users;

use Common\Models\BaseModel;


/**
 * Class UserNodePosition
 * @package Common\Models\Users
 */
class UserNodePosition extends BaseModel
{
    /**
     * @var string
     */
    public $table = 'user_node_positions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
		'user_id',
		'data',
	];

    /**
     * @return \Common\Models\Traits\BelongsTo
     */
	public function user()
    {
		return $this->belongsTo(User::class, 'user_id');
	}
}