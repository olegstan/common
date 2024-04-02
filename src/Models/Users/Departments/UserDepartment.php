<?php

namespace Common\Models\Users\Departments;

use Common\Models\BaseModel;
use Common\Models\Users\User;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserDepartment extends BaseModel
{
    /**
     * @var string
     */
    public $table = 'user_departments';

    /**
     * @var string[]
     */
    protected $fillable = [
        'department_id',
        'user_id',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'department_id' => 'string',
        'user_id' => 'integer',
    ];

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return mixed
     */
    public function department(): HasOne
    {
        return $this->hasOne(Department::class, 'id', 'department_id');
    }
}