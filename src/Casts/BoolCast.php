<?php

namespace Common\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class BoolCast implements CastsAttributes
{
    /**
     * @param $model
     * @param $key
     * @param $value
     * @param $attributes
     *
     * @return bool|null
     */
    public function get($model, $key, $value, $attributes): ?bool
    {
        if($value === false || $value)
        {
            return (bool) $value;
        }

        return null;
    }

    /**
     * @param $model
     * @param $key
     * @param $value
     * @param $attributes
     *
     * @return bool|null
     */
    public function set($model, $key, $value, $attributes): ?bool
    {
        if($value === false || $value)
        {
            return (bool) $value;
        }

        return null;
    }
}