<?php

namespace Common\Models\Users\Roles\Types;

use Common\Models\Users\Collective\UserCollectiveGroup;
use Common\Models\Users\Departments\Department;
use Common\Models\Users\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Owner
 * @package Common\Models\Users
 */
class Owner extends User
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
    public function departmens(): HasMany
    {
        return $this->hasMany(Department::class, 'user_id');
    }
}
