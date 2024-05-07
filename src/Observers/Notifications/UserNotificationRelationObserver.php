<?php

namespace Common\Observers\Notifications;

use Common\Models\Users\Notification\UserNotification;
use Common\Models\Users\Notification\UserNotificationRelation;

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
        //TODO оптимизировать, чтобы это проверялось только один раз, для одного уведомления за запрос, в конце
        $parent = UserNotification::where('id', $model->notification_id)
            ->with('items')
            ->first();

        if($parent)
        {
            //считаем все связанные итемы в уведомлениии
            //если 0 неподтвержденных, значит всё уведомление может поменять статус
            $countNotConfirmed = 0;

            $parent->items()->each(function ($item) use (&$countNotConfirmed){
                /**
                 * @var UserNotificationRelation $item
                 */
                if(!$item->is_confirmed)
                {
                    $countNotConfirmed++;
                }
            });

            if($countNotConfirmed === 0)
            {
                $parent->update([
                    'status' => UserNotification::CONFIRMED
                ]);
            }
        }
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