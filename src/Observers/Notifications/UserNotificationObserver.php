<?php

namespace Common\Observers\Notifications;

use Common\Models\Users\Notification\UserNotification;

class UserNotificationObserver
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
        $model->items()->chunkById(1000, function ($items) {
            foreach ($items as $item) {
                $item->delete();
            }
        });
    }
}