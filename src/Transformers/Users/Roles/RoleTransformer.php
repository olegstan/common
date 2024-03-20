<?php

namespace Common\Transformers\Users\Roles;

use LaravelRest\Http\Transformers\BaseTransformer;

class RoleTransformer extends BaseTransformer
{
    /**
     * @param $model
     * @return array
     */
    public function transform($model)
    {
        $data = [
            'id' => $model->id,
            'role' => 'client',
            'email' => $model->email,
            'phone' => $model->phone,
            'first_name' => $model->first_name,
            'last_name' => $model->last_name,
            'middle_name' => $model->middle_name,
            'sex' => $model->sex,

            'retired_age' => $model->retired_age,
            'dead_age' => $model->dead_age,

            'manager_id' => $model->manager_id,
            'avatar' => $model->getAvatar(),

            'percent_positive' => $model->percent_positive,
            'percent_neutral' => $model->percent_neutral,
            'percent_negative' => $model->percent_negative,
            'index_positive_income' => $model->index_positive_income,
            'index_neutral_income' => $model->index_neutral_income,
            'index_negative_income' => $model->index_negative_income,
            'index_outcome' => $model->index_outcome,
            'career_start_month' => $model->career_start_month - 1,//минус 1 потому что на фронте индекс начинается с 0
            'has_zen_access_token' => $model->count_zenmoney_data,
            'zenmoney_data' => $model->zenmoney_data_with_logins,
            'has_tinkoff_token' => (bool)$model->tinkoff_token,
            'is_visible_spend' => $model->is_visible_spend,
            'is_allow_api_operation' => $model->is_allow_api_operation,
            'currency_id' => $model->currency_id,
            'language_id' => $model->language_id,
            'points' => $model->points,

            'vk' => $model->vk,
            'fb' => $model->fb,
            'twit' => $model->twit,
            'rating' => $model->rating,
        ];

        $data = array_merge($data, $this->transformDate($model, 'birth_at'));
        $data = array_merge($data, $this->transformDate($model, 'start_enter_at'));

        return $this->withRelations($data, $model);
    }

}