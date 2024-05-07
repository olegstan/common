<?php

namespace Common\Observers\Notifications;

use Common\Models\Users\Notification\UserNotification;

class UserNotificationRelationObserver
{
    /**
     * @param UserNotification $model
     *
     * @return void
     */
    public function creating(UserNotification $model)
    {

    }

    /**
     * @param UserNotification $model
     *
     * @return void
     */
    public function created(UserNotification $model)
    {
        
    }

    /**
     * @param UserNotification $model
     *
     * @return void
     */
    public function updated(UserNotification $model)
    {
    }

    /**
     * @param UserNotification $model
     *
     * @return void
     */
    public function deleted(UserNotification $model)
    {

    }

    /**
     * @param UserNotification $model
     *
     * @return void
     */
    public function deleting(UserNotification $model)
    {
    }
}