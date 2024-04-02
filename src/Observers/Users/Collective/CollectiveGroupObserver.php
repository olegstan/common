<?php

namespace Common\Observers\Users\Collective;


use Common\Models\Users\Collective\UserCollectiveGroup;

class CollectiveGroupObserver
{
    /**
     * @param UserCollectiveGroup $data
     *
     * @return void
     */
    public function creating(UserCollectiveGroup $data)
    {

    }

    /**
     * @param UserCollectiveGroup $data
     *
     * @return void
     */
    public function created(UserCollectiveGroup $data)
    {

    }

    /**
     * @param UserCollectiveGroup $data
     *
     * @return void
     */
    public function updated(UserCollectiveGroup $data)
    {
    }

    /**
     * @param UserCollectiveGroup $data
     *
     * @return void
     */
    public function deleted(UserCollectiveGroup $data)
    {

    }

    /**
     * @param UserCollectiveGroup $data
     *
     * @return void
     */
    public function deleting(UserCollectiveGroup $data)
    {
    }
}