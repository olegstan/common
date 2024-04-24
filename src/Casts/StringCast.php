<?php

namespace Common\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class StringCast implements CastsAttributes
{
    /**
     * @param $model
     * @param $key
     * @param $value
     * @param $attributes
     *
     * @return string
     */
    public function get($model, $key, $value, $attributes): string
    {
        return (string) $value;
    }

    /**
     * @param $model
     * @param $key
     * @param $value
     * @param $attributes
     *
     * @return string
     */
    public function set($model, $key, $value, $attributes): string
    {
        return (string) $value;
    }
}