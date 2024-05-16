<?php

namespace Common\Jobs\Base;

use Common\Jobs\Interfaces\JobInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class Job implements JobInterface
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Количество попыток выполнения задания.
     *
     * @var int
     */
    public int $tries = 25;

    /**
     * Максимальное количество разрешенных необработанных исключений.
     *
     * @var int
     */
    public int $maxExceptions = 3;
}
