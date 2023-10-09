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
    ];

    /**
     * @param $callback
     * @param $id
     * @return false
     */
    public static function confirm($callback, $id)
    {
        $callbackResult = $callback();

        if($callbackResult)
        {
            $item = UserNotificationRelation::where('id', $id)
                ->first();

            if($item && $item->update([
                'is_confirmed' => true
            ]))
            {
                return $callbackResult;
            }
        }

        return false;
    }

    /**
     * @param $callback
     * @param $ids
     * @return mixed
     */
    public static function confirmMany($callback, $ids)
    {
        $callbackResult = $callback();

        if($callbackResult)
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

            if($result)
            {
                return $callbackResult;
            }
        }
    }



    /**
     * @var bool
     */
    public $timestamps = false;
}
