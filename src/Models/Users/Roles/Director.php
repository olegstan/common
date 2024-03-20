<?php

namespace Common\Models\Users\Roles;

use Common\Models\Users\User;

/**
 * Class Director
 * @package App\Models\Users
 */
class Director extends User
{
    /**
     * @var string
     */
    public $role = User::DIRECTOR;
}
