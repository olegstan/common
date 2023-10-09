<?php

namespace Common\Transformers\Users;

use Common\Models\Users\UserNotificationRelation;
use LaravelRest\Http\Transformers\BaseTransformer;

class UserNotificationRelationTransformer extends BaseTransformer
{
    /**
     * @param UserNotificationRelation $model
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'notification_id' => $model->notification_id,
            'post_id' => $model->post_id,
            'post_type' => $model->post_type,
            'comment' => $model->comment,
            'is_confirmed' => $model->is_confirmed,
        ];

        return $this->withRelations($data, $model);
    }

}