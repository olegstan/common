<?php

namespace Common\Models\Traits\Users\UserNotification;

use Common\Models\Users\UserNotificationRelation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait UserNotificationAttributeTrait
{
    /**
     * @param $data
     * @return void
     */
    public function setDataAttribute($data)
    {
        if (isset($data)) {
            if (is_array($data)) {
                $this->attributes['data'] = json_encode($data);
            } else {
                $this->attributes['data'] = $data;
            }
        }
    }

    /**
     * @param $data
     * @return void
     */
    public function getDataAttribute($data)
    {
        return json_decode($data);
    }
}