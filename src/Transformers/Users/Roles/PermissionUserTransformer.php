<?php

namespace Common\Transformers\Users\Roles;

use Common\Models\Users\Roles\PermissionUser;
use LaravelRest\Http\Transformers\BaseTransformer;

class PermissionUserTransformer extends BaseTransformer
{
    /**
     * @param PermissionUser $model
     *
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'permission_id' => $model->permission_id,
            'user_id' => $model->user_id,
        ];

        return $this->withRelations($data, $model);
    }

}