<?php

namespace Common\Transformers\Users\Crm;
use Common\Models\Users\Crm\UserConfig;
use LaravelRest\Http\Transformers\BaseTransformer;

class UserConfigTransformer extends BaseTransformer
{
    /**
     * @param UserConfig $model
     *
     * @return mixed
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'user_id' => $model->user_id,
            'type' => $model->type,
            'key' => $model->key,
            'value' => $model->value,
        ];

        return $this->withRelations($data, $model);
    }

}
