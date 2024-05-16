<?php

namespace Common\Jobs\Traits;

trait DestructBaseJobTrait
{
    public function rollingStaticValues()
    {
        foreach ($this->staticValues as $namespace => $staticValue) {
            dd($namespace, $staticValue);
        }
    }
}