<?php

namespace Common\Models\Traits\Users\Roles;

trait UserPathTrait
{
    /**
     * @return string
     */
    public static function avatarPath(): string
    {
        return public_path() . self::$avatarPath;
    }

    /**
     * @return string
     */
    public static function documentPath(): string
    {
        return public_path() . self::$documentPath;
    }

    /**
     * @return string
     */
    public function getAvatar(): string
    {
        if ($this->avatar) {
            $path = self::$avatarPath . $this->avatar;
            if (file_exists(public_path() . $path)) {
                return config('app.url') . $path;
            }
        }

        return config('app.url') . self::$avatarPath . 'default.svg';
    }
}