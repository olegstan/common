<?php

namespace Common\Observers\Users\Roles;

use Common\Models\Users\Roles\Types\Owner;

class OwnerObserver
{
    /**
     * @param Owner $user
     *
     * @return void
     */
    public function creating(Owner $user)
    {

    }

    /**
     * @param Owner $user
     *
     * @return void
     */
    public function created(Owner $user)
    {
        $user->createAccounts();
    }

    /**
     * @param Owner $user
     *
     * @return void
     */
    public function updated(Owner $user)
    {
    }

    /**
     * @param Owner $user
     *
     * @return void
     */
    public function deleted(Owner $user)
    {

    }

    /**
     * @param Owner $user
     *
     * @return void
     */
    public function deleting(Owner $user)
    {
    }
}