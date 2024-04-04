<?php

namespace Common\Models\Users\Departments;

use Common\Models\BaseModel;
use Common\Models\Users\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Department extends BaseModel
{
    /**
     * @var string
     */
    public $table = 'departments';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'user_id' => 'integer',
        'name' => 'string',
        'description' => 'string',
    ];

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return HasMany
     */
    public function user_departments(): HasMany
    {
        return $this->hasMany(UserDepartment::class, 'department_id');
    }
}