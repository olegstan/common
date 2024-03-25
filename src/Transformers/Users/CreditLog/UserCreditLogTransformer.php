<?php

namespace Common\Transformers\Users\CreditLog;
use Common\Models\Users\CreditLog\UserCreditLog;
use LaravelRest\Http\Transformers\BaseTransformer;

class UserCreditLogTransformer extends BaseTransformer
{
    /**
     * @param UserCreditLog $model
     *
     * @return mixed
     */
    public function transform(UserCreditLog $model)
    {
        $data = [
            'user_id' => $model->user_id,
            'comment' => $model->comment,
            'credits_before' => $model->credits_before,
            'credits_after' => $model->credits_after,
            'points_before' => $model->points_before,
            'points_after' => $model->points_after,
            'sum' => $model->sum,
            'point_sum' => $model->point_sum,
            'created_at' => $model->created_at,
            'type_id' => $model->type_id,
        ];

        return $this->withRelations($data, $model);
    }

}
