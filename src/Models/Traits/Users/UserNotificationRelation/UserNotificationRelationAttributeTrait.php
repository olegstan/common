<?php

namespace Common\Models\Traits\Users\UserNotificationRelation;

use Common\Helpers\Helper;

trait UserNotificationRelationAttributeTrait
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
     *
     * @return array|object
     */
    public function getDataAttribute($data)
    {
        return Helper::object_to_array(json_decode($data));
    }
}
