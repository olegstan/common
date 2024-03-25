<?php

namespace Common\Models\Users\Roles\Types;

use Common\Models\Users\User;

/**
 * Class Assistant
 * @package Common\Models\Users
 */
class Assistant extends User
{
    /**
     * @var string
     */
    public $role = User::ASSISTANT;
}
