<?php

namespace Common\Models\Users\Roles\Types;

use Common\Models\Users\User;

/**
 * Class Director
 * @package Common\Models\Users
 */
class Director extends User
{
    /**
     * @var string
     */
    public $role = User::DIRECTOR;
}
