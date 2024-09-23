<?php

namespace Common\Models\Users\Roles\Types;

use Common\Models\Users\Employee;
use Common\Models\Users\User;

/**
 * Class Assistant
 * @package Common\Models\Users
 */
class Assistant extends Employee
{
    /**
     * @var string
     */
    public $role = User::ASSISTANT;
}
