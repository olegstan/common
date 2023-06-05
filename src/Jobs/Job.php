<?php

namespace Common\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class Job implements ShouldQueue
{
    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "queueOn" and "delay" queue helper methods.
    |
    */

    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var bool чтобы не было ошибки по таймауту
     */
    public bool $failOnTimeout = false;

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
