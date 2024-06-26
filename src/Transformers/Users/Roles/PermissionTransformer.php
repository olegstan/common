<?php

namespace Common\Transformers\Users\Roles;

use Common\Models\Users\Roles\Permission;
use LaravelRest\Http\Transformers\BaseTransformer;

class PermissionTransformer extends BaseTransformer
{
    /**
     * @param Permission $model
     *
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'name' => $model->name,
            'slug' => $model->slug,
            'description' => $model->description,
            'model' => $model->model,
        ];

        return $this->withRelations($data, $model);
    }

}