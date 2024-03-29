<?php

namespace Common\Models\Users\Roles\Types;

use App\Models\Crm\CrmResources;
use Common\Models\Users\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Manager
 * @package Common\Models\Users
 */
class Manager extends User
{
    /**
     * @var string
     */
    public $role = User::MANAGER;

    /**
     * @return HasMany
     */
    public function resources(): HasMany
    {
        return $this->hasMany(CrmResources::class, 'user_id');
    }
}
