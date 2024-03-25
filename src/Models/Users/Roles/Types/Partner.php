<?php

namespace Common\Models\Users\Roles\Types;

use Common\Models\Users\User;

/**
 * Class Partner
 * @package Common\Models\Users
 */
class Partner extends User
{
    /**
     * @var string
     */
    public $role = User::PARTNER;
}
