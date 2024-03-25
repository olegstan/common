<?php

namespace Common\Transformers\Users\Notification;

use Common\Models\Users\Notification\UserNotification;
use LaravelRest\Http\Transformers\BaseTransformer;

class UserNotificationTransformer extends BaseTransformer
{
    /**
     * @param UserNotification $model
     *
     * @return array
     */
    public function transform(UserNotification $model)
    {
        $data = [
            'id' => $model->id,
            'content' => $model->content,
            'user_id' => $model->user_id,
            'status' => $model->status,
            'action_id' => $model->action_id,
            'data' => $model->data,
        ];

        $data = array_merge($data, $this->transformDate($model, 'created_at'));

        return $this->withRelations($data, $model);
    }

}