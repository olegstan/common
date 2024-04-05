<?php

namespace Common\Transformers\Users\Roles;

use Common\Models\Users\Roles\Role;
use LaravelRest\Http\Transformers\BaseTransformer;

class RoleTransformer extends BaseTransformer
{
    /**
     * @param Role $model
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'name' => $model->name,
            'slug' => $model->slug,
            'description' => $model->description,
            'level' => $model->level,
        ];

        return $this->withRelations($data, $model);
    }

}