<?php

namespace Common\Models\Users\Roles;

use Common\Models\Users\User;

/**
 * Class Owner
 * @package App\Models\Users
 */
class Owner extends User
{
    /**
     * @var string
     */
    public $role = User::OWNER;
}
