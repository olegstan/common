<?php

namespace Common\Models\Users\Roles;

use Common\Models\Users\User;

/**
 * Class Accountant
 * @package App\Models\Users
 */
class Accountant extends User
{
    /**
     * @var string
     */
    public $role = User::ACCOUNTANT;
}
