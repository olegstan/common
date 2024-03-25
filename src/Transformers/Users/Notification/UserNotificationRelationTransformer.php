<?php

namespace Common\Transformers\Users\Notification;

use Common\Models\Users\Notification\UserNotificationRelation;
use LaravelRest\Http\Transformers\BaseTransformer;

class UserNotificationRelationTransformer extends BaseTransformer
{
    /**
     * @param UserNotificationRelation $model
     *
     * @return array
     */
    public function transform(UserNotificationRelation $model)
    {
        $data = [
            'id' => $model->id,
            'notification_id' => $model->notification_id,
            'post_id' => $model->post_id,
            'post_type' => $model->post_type,
            'comment' => $model->comment,
            'is_confirmed' => $model->is_confirmed,
            'data' => $model->data,
        ];

        return $this->withRelations($data, $model);
    }

}