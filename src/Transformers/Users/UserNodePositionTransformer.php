<?php

namespace Common\Transformers\Users;

use Common\Models\Users\UserNodePosition;
use LaravelRest\Http\Transformers\BaseTransformer;

class UserNodePositionTransformer extends BaseTransformer
{
    /**
     * @param UserNodePosition $model
     *
     * @return mixed
     */
    public function transform($model)
    {
        $data = [
            'user_id' => $model->user_id,
            'data' => $model->data,
        ];

        return $this->withRelations($data, $model);
    }

}
