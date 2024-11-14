<?php

namespace Common\Observers\Users\Roles;

use Cache;
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

    }

    /**
     * @param Owner $user
     *
     * @return void
     */
    public function updated(Owner $user)
    {
        Cache::tags(config('cache.tags'))->forger('token.auth.user' . $user->api_token);
    }

    /**
     * @param Owner $user
     *
     * @return void
     */
    public function deleted(Owner $user)
    {
        Cache::tags(config('cache.tags'))->forger('token.auth.user' . $user->api_token);
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