<?php

namespace Common\Models\Users;

use Common\Models\BaseModel;

/**
 * Class UserLogin
 * @package App\Models
 */
class UserLogin extends BaseModel
{
    /**
     * @var string
     */
    public $table = 'user_logins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
		'user_id',
		'ip',
		'country',
	];
	
    /**
     * @var bool
     */
    protected $timestamp = true;

    /**
     * @return \Common\Models\Traits\BelongsTo
     */
	public function user()
    {
		return $this->belongsTo(User::class, 'user_id');
	}
}