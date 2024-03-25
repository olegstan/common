<?php

namespace Common\Transformers\Users\Plan;
use Common\Models\Users\Plan\UserUpdatePlan;
use LaravelRest\Http\Transformers\BaseTransformer;

class UserUpdatePlanTransformer extends BaseTransformer
{
    /**
     * @param UserUpdatePlan $model
     *
     * @return mixed
     */
    public function transform(UserUpdatePlan $model)
    {
        $data = [
            'id' => $model->id,
            'user_id' => $model->user_id,
            'data' => $model->data,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
            'type_id' => $model->type_id,
        ];

        return $this->withRelations($data, $model);
    }
}
