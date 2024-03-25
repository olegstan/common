<?php

namespace Common\Transformers\Users\CategoryBudget;

use Common\Models\Users\CategoryBudget\UserCategoryBudget;
use LaravelRest\Http\Transformers\BaseTransformer;

class UserCategoryBudgetTransformer extends BaseTransformer
{
    /**
     * @param UserCategoryBudget $model
     *
     * @return mixed
     */
    public function transform(UserCategoryBudget $model)
    {
        $data = [
            'id' => $model->id,
            'user_id' => $model->user_id,
            'is_active' => $model->is_active,
            'name' => $model->name,
        ];

        return $this->withRelations($data, $model);
    }

}