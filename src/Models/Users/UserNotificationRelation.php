<?php

namespace Common\Models\Users;

use Common\Models\BaseModel;
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
 */
class UserNotificationRelation extends BaseModel
{
    use UserNotificationRelationsTrait;

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
    ];

    /**
     * @var array
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
     * @param $callback
     * @param $id
     * @return false
     */
    public static function confirm($callback, $id)
    {
        [$callbackResponse, $callbackResult] = $callback();

        if($callbackResult)
        {
            $item = UserNotificationRelation::where('id', $id)
                ->first();

            if($item && $item->update([
                'is_confirmed' => true
            ]))
            {
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

        if($callbackResult && is_array($ids) && $ids)
        {
            $result = true;
            $items = UserNotificationRelation::whereIn('id', $ids)
                ->get();

            foreach ($items as $item)
            {
                $item->update([
                    'is_confirmed' => true
                ]);
            }
        }

        return $callbackResponse;
    }



    /**
     * @var bool
     */
    public $timestamps = false;
}
