<?php

namespace Common\Models\Traits\Users\Roles;

use Illuminate\Support\Str;

trait Slugable
{
    /**
     * Set slug attribute.
     *
     * @param string $value
     * @return void
     */
    public function setSlugAttribute(string $value)
    {
        $this->attributes['slug'] = Str::slug($value, config('roles.separator'));
    }
}
