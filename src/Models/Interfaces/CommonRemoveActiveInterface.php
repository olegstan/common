<?php

namespace Common\Models\Interfaces;

interface CommonRemoveActiveInterface
{
    /**
     * @param $user
     * @param $collections
     * @return mixed
     */
    public function selfRemoveData($user, $collections): void;
}