<?php

namespace Common\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class IntegerCast implements CastsAttributes
{
    /**
     * @param $model
     * @param $key
     * @param $value
     * @param $attributes
     *
     * @return int|null
     */
    public function get($model, $key, $value, $attributes): ?int
    {
        if (is_numeric($value)) {
            return (int)$value;
        }

        return null;
    }

    /**
     * @param $model
     * @param $key
     * @param $value
     * @param $attributes
     *
     * @return int|null
     */
    public function set($model, $key, $value, $attributes): ?int
    {
        if (is_numeric($value)) {
            return (int)$value;
        }

        return null;
    }
}