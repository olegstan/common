<?php

namespace Common\Transformers\Users;

use Common\Models\Users\Notification\UserNotification;
use Common\Transformers\Users\Notification\UserNotificationTransformer;
use Auth;
use Common\Models\Users\User;
use LaravelRest\Http\Transformers\BaseTransformer;

class UserProfileTransformer extends BaseTransformer
{
    /**
     * @param User $model
     * @return array
     */
    public function transform($model)
    {
        $last_notification = UserNotification::lastByUserId((int) $model->id)->where('status', UserNotification::CREATED)->first();

        $data = [
            'id' => $model->id,
            'role' => Auth::getRole(),
            'email' => $model->email,
            'phone' => $model->phone,
            'first_name' => $model->first_name,
            'last_name' => $model->last_name,
            'middle_name' => $model->middle_name,
            'sex' => $model->sex,

            'retired_age' => $model->retired_age,
            'dead_age' => $model->dead_age,

            'manager_id' => $model->manager_id,
            'data' => json_decode($model->data),
            'avatar' => $model->getAvatar(),
            'scenario' => json_decode($model->scenario),

            'vk' => $model->vk,
            'fb' => $model->fb,
            'twit' => $model->twit,

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
            'last_notification' => $last_notification ? (new UserNotificationTransformer())->transform($last_notification) : null,
            'has_tinkoff_token' => (bool)$model->tinkoff_token,
            'gauth_trigger' => (bool)$model->gauth_trigger,
            'is_visible_spend' => $model->is_visible_spend,
            'is_allow_api_operation' => $model->is_allow_api_operation,
            'currency_id' => $model->currency_id,
            'language_id' => $model->language_id,
            'points' => $model->points,
            'api_token' => $model->api_token,
        ];

        $data = array_merge($data, $this->transformDate($model, 'birth_at'));
        $data = array_merge($data, $this->transformDate($model, 'start_enter_at'));

        return $this->withRelations($data, $model);
    }

}