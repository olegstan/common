<?php

namespace Common\Models\Users\Roles\Types;

use Common\Models\Users\Employee;
use Common\Models\Users\User;

/**
 * Class Accountant
 * @package Common\Models\Users
 */
class Accountant extends Employee
{
    /**
     * @var string
     */
    public $role = User::ACCOUNTANT;
}
