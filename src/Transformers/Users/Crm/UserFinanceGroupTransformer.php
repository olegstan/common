<?php

namespace Common\Transformers\Users\Crm;

use Common\Models\Users\Crm\UserFinanceGroup;
use LaravelRest\Http\Transformers\BaseTransformer;

/**
 * Class UserFinanceGroupF
 * @package App\Api\V1\Transformers\Base\Users
 */
class UserFinanceGroupTransformer extends BaseTransformer
{
    /**
     * @param UserFinanceGroup $model
     *
     * @return mixed
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'user_id' => $model->user_id,
            'union_user_id' => $model->union_user_id,
            'type_id' => $model->type_id,
        ];

        return $this->withRelations($data, $model);
    }

}