<?php

namespace Common\Models\Users\Roles\Types;

use Common\Models\Users\Employee;
use Common\Models\Users\User;

/**
 * Class Driver
 * @package Common\Models\Users
 */
class Driver extends Employee
{
    /**
     * @var string
     */
    public $role = User::DRIVER;
}
