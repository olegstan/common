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

    /**
     * Все привязанные сотрудники
     *
     * @return HasMany
     */
    public function user_employee_groups(): HasMany
    {
        return $this->hasMany(UserCollectiveGroup::class, 'user_id')->where('type_id', UserCollectiveGroup::EMPLOYEE);
    }

    /**
     * Все привязанные члены семьи
     *
     * @return HasMany
     */
    public function user_family_groups(): HasMany
    {
        return $this->hasMany(UserCollectiveGroup::class, 'user_id')->where('type_id', UserCollectiveGroup::FAMILY);
    }

    /**
     * Все привязанные пользователи
     *
     * @return HasMany
     */
    public function user_all_groups(): HasMany
    {
        return $this->hasMany(UserCollectiveGroup::class, 'user_id');
    }
}
