<?php

namespace Common\Models\Traits\Users\UserNotification;

use Common\Models\Traits\Users\User;
use Common\Models\Users\UserNotificationRelation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait UserNotificationRelationsTrait
{
    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function relations(): HasMany
    {
        return $this->hasMany(UserNotificationRelation::class, 'notification_id', 'id');
    }
}
