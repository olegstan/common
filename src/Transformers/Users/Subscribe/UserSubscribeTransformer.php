<?php

namespace Common\Transformers\Users\Subscribe;
use Common\Models\Users\Subscribe\UserSubscribe;
use LaravelRest\Http\Transformers\BaseTransformer;

class UserSubscribeTransformer extends BaseTransformer
{
    /**
     * @param \Common\Models\Users\Subscribe\UserSubscribe $model
     *
     * @return mixed
     */
    public function transform(UserSubscribe $model)
    {
        $data = [
            'subscribe_id' => $model->subscribe_id,
            'order_id' => $model->order_id,
            'start_at' => $model->start_at,
            'end_at' => $model->end_at,
        ];

        return $this->withRelations($data, $model);
    }

}
