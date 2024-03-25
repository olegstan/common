<?php

namespace Common\Transformers\Users\Auth;

use Common\Models\Users\Auth\UserAuthToken;
use LaravelRest\Http\Transformers\BaseTransformer;

class UserAuthTokenTransformer extends BaseTransformer
{
    /**
     * @param UserAuthToken $model
     *
     * @return array
     */
    public function transform(UserAuthToken $model)
    {
        $data = [
            'user_id' => $model->user_id,
            'token' => $model->token,
        ];

        return $this->withRelations($data, $model);
    }

}