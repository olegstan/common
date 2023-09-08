<?php

namespace Common\Transformers\Users;

use Common\Models\Users\UserNotification;
use LaravelRest\Http\Transformers\BaseTransformer;

class UserNotificationTransformer extends BaseTransformer
{
    /**
     * @param UserNotification $model
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'content' => $model->content,
            'user_id' => $model->user_id,
            'status' => $model->status,
            'created_at' => $model->created_at ? $model->created_at->format('H:i:s d.m.Y') : '',
            'action_id' => $model->action_id,
        ];

        return $this->withRelations($data, $model);
    }

}