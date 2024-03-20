<?php

namespace Common\Models\Users\Roles;

use Common\Models\Users\User;

/**
 * Class Manager
 * @package App\Models\Users
 */
class Manager extends User
{
    /**
     * @var string
     */
    public $role = User::MANAGER;
}
