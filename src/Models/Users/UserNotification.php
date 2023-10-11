<?php

namespace Common\Models\Users;

use Common\Models\BaseModel;
use Common\Models\Traits\Users\UserNotification\UserNotificationRelationsTrait;
use Common\Models\Traits\Users\UserNotification\UserNotificationScopeTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property $content
 * @property $user_id
 * @property $status
 * @property $action_id
 */
class UserNotification extends BaseModel
{
    use UserNotificationRelationsTrait;
    use UserNotificationScopeTrait;

    /**
     * status
     */
    public const CREATED = 1001;
    public const READED = 2001;
    public const CONFIRMED = 3001;
    public const REFUSED = 4001;
    public const INFO = 5001;

    /**
     * action type
     */
    public const TRANSFERED_STOCK = 1001;
    public const CLOSE_INSURANCES = 1002;
    public const TOKEN_IS_NOT_VALID = 1003;

    public const CONTACT_BIRTHDAY = 2003;

    /**
     * @var string
     */
    protected $table = 'user_notifications';

    /**
     * @var string[]
     */
    protected $fillable = [
        'content',
        'user_id',
        'status',
        'action_id',
    ];

    /**
     * @param array $attributes = [
     *     'user_id' => (int),
     *     'content' => (string),
     *     'action_id' => (int),
     *     'status' => (int)
     * ]
     * @return UserNotification|BaseModel|null
     */
    public static function createRelations(array $attributes)
    {
        $notification = UserNotification::whereUserId($attributes['user_id'])
            ->whereContent($attributes['content'])
            ->whereActionId($attributes['action_id'])
            ->whereStatus($attributes['status'])
            ->first();

        if ($notification) {
            return $notification;
        }

        $notification = UserNotification::create([
            'user_id' => $attributes['user_id'],
            'action_id' => $attributes['action_id'],
            'content' => $attributes['content'],
            'status' => $attributes['status'],
        ]);

        if (!$notification) {
            return null;
        }

        if (isset($attributes['relations'])) {
            foreach ($attributes['relations'] as $relation) {
                UserNotificationRelation::create([
                    'notification_id' => $notification->id,
                    'post_id' => $relation['post_id'],
                    'post_type' => $relation['post_type'],
                    'local_relation' => $relation['local_relation'] ?? null,
                ]);
            }
        }

        return $notification;
    }
}
