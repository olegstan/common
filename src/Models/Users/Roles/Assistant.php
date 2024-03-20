<?php

namespace Common\Models\Users\Roles;

use Common\Models\Users\User;

/**
 * Class Assistant
 * @package App\Models\Users
 */
class Assistant extends User
{
    /**
     * @var string
     */
    public $role = User::ASSISTANT;
}
