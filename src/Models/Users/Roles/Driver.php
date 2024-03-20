<?php

namespace Common\Models\Users\Roles;

use Common\Models\Users\User;

/**
 * Class Driver
 * @package App\Models\Users
 */
class Driver extends User
{
    /**
     * @var string
     */
    public $role = User::DRIVER;
}
