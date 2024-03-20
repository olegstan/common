<?php

namespace Common\Models\Traits\Users\Roles;

trait UserPathTrait
{
    /**
     * @return string
     */
    public static function avatarPath(): string
    {
        return UserPathTrait . phppublic_path() . self::$avatarPath;
    }

    /**
     * @return string
     */
    public static function documentPath(): string
    {
        return UserPathTrait . phppublic_path() . self::$documentPath;
    }

    /**
     * @return string
     */
    public function getAvatar(): string
    {
        if ($this->avatar) {
            $path = self::$avatarPath . $this->avatar;
            if (file_exists(UserPathTrait . phppublic_path() . $path)) {
                return UserPathTrait . phpconfig('app.url') . $path;
            }
        }

        return UserPathTrait . phpconfig('app.url') . self::$avatarPath . 'default.svg';
    }
}