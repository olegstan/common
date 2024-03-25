<?php

namespace Common\Transformers\Users;

use Common\Models\Users\UserLogin;
use LaravelRest\Http\Transformers\BaseTransformer;

class UserLoginTransformer extends BaseTransformer
{
    /**
     * @param UserLogin $model
     *
     * @return mixed
     */
    public function transform(UserLogin $model)
    {
        $data = [
            'user_id' => $model->user_id,
            'ip' => $model->ip,
            'country' => $model->country,
        ];

        return $this->withRelations($data, $model);
    }

}
