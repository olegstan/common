<?php

namespace Common\Jobs\Interfaces;

interface JobInterface
{
    /**
     * У каждой очереди должен быть указан свой тип
     * для разделения
     */
    public const TYPE = 0;
}