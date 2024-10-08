<?php
namespace Common\Models\Traits\Users;


use Common\Models\Users\Roles\Types\Client;

/**
 * Trait UserTrait
 *
 * @mixin Client
 *
 * @package Common\Models\Traits\Users
 */
trait UserTrait
{
    /**
     * @return string
     */
    public function getFio()
    {
        return $this->last_name . ' ' . $this->first_name . ($this->middle_name ? ' ' . $this->middle_name : '');
    }
}