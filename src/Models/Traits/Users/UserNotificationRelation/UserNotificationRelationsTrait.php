<?php

namespace Common\Models\Traits\Users\UserNotificationRelation;

use Common\Models\Users\UserNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait UserNotificationRelationsTrait
{

    /**
     * @return MorphTo
     */
    public function post(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo
     */
    public function local_operation(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasOne
     */
    public function notification()
    {
        return $this->hasOne(UserNotification::class, 'id', 'notification_id');
    }
}
