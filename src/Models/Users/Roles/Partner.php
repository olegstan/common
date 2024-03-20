<?php

namespace Common\Models\Users\Roles;

use Common\Models\Users\User;

/**
 * Class Partner
 * @package App\Models\Users
 */
class Partner extends User
{
    /**
     * @var string
     */
    public $role = User::PARTNER;
}
