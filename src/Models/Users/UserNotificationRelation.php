<?php

namespace Common\Models\Users;

use Common\Models\BaseModel;
use Common\Models\Traits\Users\UserNotificationRelation\UserNotificationRelationAttributeTrait;
use Common\Models\Traits\Users\UserNotificationRelation\UserNotificationRelationsTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property $notification_id
 * @property $post_type
 * @property $post_id
 * @property $comment
 * @property $is_confirmed
 * @property $local_operation_type
 * @property $local_operation_id
 * @property $data
 */
class UserNotificationRelation extends BaseModel
{
    use UserNotificationRelationsTrait;
    use UserNotificationRelationAttributeTrait;

    /**
     * @var string
     */
    protected $table = 'user_notification_relations';

    /**
     * @var string[]
     */
    protected $fillable = [
        'notification_id',
        'post_type',
        'post_id',
        'comment',
        'is_confirmed',
        'local_operation_type',
        'local_operation_id',
        'data',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'notification_id' => 'integer',
        'post_type' => 'string',
        'post_id' => 'integer',
        'comment' => 'string',
        'is_confirmed' => 'bool',
        'local_operation_type' => 'string',
        'local_operation_id' => 'integer',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @param $callback
     * @param $id
     * @return false
     */
    public static function confirm($callback, $id)
    {
        [$callbackResponse, $callbackResult] = $callback();

        if ($callbackResult) {
            $item = UserNotificationRelation::where('id', $id)
                ->first();

            if ($item) {
                $item->update([
                    'is_confirmed' => true
                ]);
            }
        }

        return $callbackResponse;
    }

    /**
     * @param $callback
     * @param $ids
     * @return mixed
     */
    public static function confirmMany($callback, $ids)
    {
        [$callbackResponse, $callbackResult] = $callback();

        if ($callbackResult && is_array($ids) && $ids) {
            $result = true;
            $items = UserNotificationRelation::whereIn('id', $ids)
                ->get();

            foreach ($items as $item) {
                $item->update([
                    'is_confirmed' => true
                ]);
            }
        }

        return $callbackResponse;
    }

    /**
     * @param $user
     * @param $collections
     * @return void
     */
    public function selfRemoveData($user, $collections): void
    {
        $notifications = UserNotification::whereUserId($user->id)->pluck('id');
        $selfData = UserNotificationRelation::whereIn('notification_id', $notifications)->cursor();

        foreach ($selfData as $data) {
            $collections->put($this->getTableWithoutPrefix() . '.' . $data->id, json_encode($data));
        }
    }
}
