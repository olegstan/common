<?php

namespace Common\Models\Traits\Users\UserNotification;

use Common\Models\Users\UserNotificationRelation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait UserNotificationRelationsTrait
{
    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(UserNotificationRelation::class, 'notification_id', 'id');
    }
}
