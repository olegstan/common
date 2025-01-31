<?php

namespace Common\Helpers\Queue;

use Common\Helpers\LoggerHelper;
use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Queue\ManuallyFailedException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class ExtendedDatabaseJob extends DatabaseJob
{
    /**
     * ExtendedDatabaseJob constructor.
     * @param Container $container
     * @param DatabaseQueue $database
     * @param $job
     * @param $connectionName
     * @param $queue
     */
    public function __construct(Container $container, DatabaseQueue $database, $job, $connectionName, $queue)
    {
        if (config('app.extended_log')) {
            LoggerHelper::flushListeners();
            LoggerHelper::$commandKey = 'queue';

            $payload = $job->payload;

            if ($payload) {
                $jsonDecoded = json_decode($job->payload, true);

                if (isset($jsonDecoded['job'])) {
                    $path = explode('\\', $jsonDecoded['job']);
                    $count = count($path);

                    if (isset($path[$count - 1])) {
                        LoggerHelper::$jobKey = strtolower($path[$count - 1]);
                    }
                }
            }

            Event::forget(QueryExecuted::class);
            DB::listen(function ($sql)
            {
                if (LoggerHelper::$logQuery || $sql->time > 100)
                {
                    LoggerHelper::listenQuery($sql);
                }
            });
        }

        $this->job = $job;
        $this->queue = $queue;
        $this->database = $database;
        $this->container = $container;
        $this->connectionName = $connectionName;
    }

    /**
     * @param $e
     * @return void
     */
    public function fail($e = null)
    {
        $this->markAsFailed();

        if ($this->isDeleted()) {
            return;
        }

        try {
            // If the job has failed, we will delete it, call the "failed" method and then call
            // an event indicating the job has failed so it can be logged if needed. This is
            // to allow every developer to better keep monitor of their failed queue jobs.
            $this->delete();

            $this->failed($e);
        } finally {
            ExtendedDatabaseFailedJobProvider::$user_id = $this->job->user_id;
            ExtendedDatabaseFailedJobProvider::$type_id = $this->job->type_id;

            $this->resolve(Dispatcher::class)->dispatch(new JobFailed($this->connectionName, $this, $e ?: new ManuallyFailedException));
        }
    }
}