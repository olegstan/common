<?php

namespace Common\Models\Traits\Users\UserNotification;

use Illuminate\Database\Eloquent\Builder;

trait UserNotificationScopeTrait
{
    /**
     * @param Builder $query
     * @param int $user_id
     * @return Builder
     */
    public function scopeLastByUserId(Builder $query, int $user_id): Builder
    {
        return $query->where('user_id', '=', $user_id)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');
    }
}
