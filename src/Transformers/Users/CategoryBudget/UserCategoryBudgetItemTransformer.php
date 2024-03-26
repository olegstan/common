<?php

namespace Common\Transformers\Users\CategoryBudget;

use Common\Models\Users\CategoryBudget\UserCategoryBudgetItem;
use LaravelRest\Http\Transformers\BaseTransformer;

class UserCategoryBudgetItemTransformer extends BaseTransformer
{
    /**
     * @param UserCategoryBudgetItem $model
     *
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'type_id' => $model->type_id,
            'custom_type_id' => $model->custom_type_id,
            'sum' => $model->sum,
            'parent_id' => $model->parent_id,
        ];

        return $this->withRelations($data, $model);
    }
}