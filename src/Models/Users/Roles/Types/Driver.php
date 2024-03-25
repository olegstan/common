<?php

namespace Common\Models\Users\Roles\Types;

use Common\Models\Users\User;

/**
 * Class Driver
 * @package Common\Models\Users
 */
class Driver extends User
{
    /**
     * @var string
     */
    public $role = User::DRIVER;
}
