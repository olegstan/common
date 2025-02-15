<?php

namespace Common\Models\Users\Roles\Types;

use Common\Models\Users\Departments\Department;
use Common\Models\Users\Employee;
use Common\Models\Users\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Owner
 * @package Common\Models\Users
 */
class Owner extends Employee
{
    /**
     * @var string
     */
    public $role = User::OWNER;

    /**
     * Все отделы созданные пользователем
     *
     * @return HasMany
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'user_id');
    }
}
