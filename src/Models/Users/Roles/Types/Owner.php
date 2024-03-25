<?php

namespace Common\Models\Users\Roles\Types;

use Common\Models\Users\User;

/**
 * Class Owner
 * @package Common\Models\Users
 */
class Owner extends User
{
    /**
     * @var string
     */
    public $role = User::OWNER;
}
