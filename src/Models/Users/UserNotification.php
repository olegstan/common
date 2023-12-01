<?php

namespace Common\Models\Users;

use App\Models\Actives\ActiveGroup;
use Common\Models\BaseModel;
use Common\Models\Interfaces\CommonRemoveActiveInterface;
use Common\Models\Traits\Users\UserNotification\UserNotificationAttributeTrait;
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
 * @property $data
 * @property $api_id
 */
class UserNotification extends BaseModel implements CommonRemoveActiveInterface
{
    use UserNotificationRelationsTrait;
    use UserNotificationScopeTrait;
    use UserNotificationAttributeTrait;

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
    public const NEGATIVE_TRADES = 1004;
    public const NOT_FOUND_STOCK = 1005;
    public const NOT_FOUND_MONEY_ON_DATE = 1006;
    public const ERROR_COUNT_STOCK = 1007;

    public const CONTACT_BIRTHDAY = 2003;

    /**
     * api_id
     */
    public const ATON = 1;
    public const TINKOFF = 2;
    public const BCS = 3;
    public const ZENMONEY = 4;

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
        'data',
        'api_id'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'content' => 'string',
        'user_id' => 'integer',
        'status' => 'integer',
        'action_id' => 'integer',
        'api_id' => 'integer',
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
            ->where(function ($query) use ($attributes) {
                if (isset($attributes['api_id'])) {
                    $query->whereApiId($attributes['api_id']);
                }
            })
            ->first();

        if ($notification) {
            return $notification;
        }

        $notification = UserNotification::create([
            'user_id' => $attributes['user_id'],
            'action_id' => $attributes['action_id'],
            'content' => $attributes['content'],
            'status' => $attributes['status'],
            'api_id' => $attributes['api_id'] ?? null,
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

    /**
     * @param $user
     * @param $collections
     * @return void
     */
    public function selfRemoveData($user, $collections): void
    {
        $selfData = UserNotification::whereUserId($user->id)->cursor();

        foreach ($selfData as $data) {
            $collections->put($this->getTableWithoutPrefix() . '.' . $data->id, json_encode($data));
        }
    }
}
