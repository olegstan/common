<?php

namespace Common\Helpers\Queue;

use Illuminate\Queue\Failed\DatabaseFailedJobProvider;
use Illuminate\Support\Facades\Date;

class ExtendedDatabaseFailedJobProvider extends DatabaseFailedJobProvider
{
    public static ?int $user_id = null;
    public static ?int $type_id = null;

    /**
     * @param $connection
     * @param $queue
     * @param $payload
     * @param $exception
     * @return int|null
     */
    public function log($connection, $queue, $payload, $exception)
    {
        $failed_at = Date::now();

        $user_id = self::$user_id;
        $type_id = self::$type_id;

        $exception = (string) $exception;
        return $this->getTable()->insertGetId(compact(
            'connection', 'queue', 'payload', 'exception', 'failed_at', 'user_id', 'type_id'
        ));
    }
}
