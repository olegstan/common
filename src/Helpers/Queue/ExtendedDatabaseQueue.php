<?php

namespace Common\Helpers\Queue;

use Carbon\Carbon;
use DateInterval;
use DateTimeInterface;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Database\Query\Builder;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Queue\Jobs\DatabaseJobRecord;
use Throwable;

class ExtendedDatabaseQueue extends DatabaseQueue
{
    /**
     * @param $queue
     * @return ExtendedDatabaseJob|Job|void|null
     * @throws Throwable
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        try {
            $this->database->beginTransaction();

            if ($job = $this->getNextAvailableJob($queue)) {
                return $this->marshalJob($queue, $job);
            }

            $this->database->commit();
        } catch (Throwable $e) {
            $this->database->rollBack();

            throw $e;
        }
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        return $this->enqueueUsing(
            $job,
            $this->createPayload($job, $this->getQueue($queue), $data),
            $queue,
            null,
            function ($payload, $queue) {
                return $this->pushToDatabase($queue, $payload);
            }
        );
    }

    /**
     * Modify the query to check for jobs that are reserved but have expired.
     *
     * @param Builder $query
     * @return void
     */
    protected function isReservedButExpired($query)
    {
        $expiration = \Illuminate\Support\Carbon::now()->subSeconds($this->retryAfter);

        $query->orWhere(function ($query) use ($expiration) {
            $query->where('reserved_at', '<=', $expiration);
        });
    }

    /**
     * Modify the query to check for available jobs.
     *
     * @param  Builder  $query
     * @return void
     */
    protected function isAvailable($query)
    {
        $query->where(function ($query) {
            $query->whereNull('reserved_at')
                ->where('available_at', '<=', Carbon::now());
        });
    }

    /**
     * Marshal the reserved job into a DatabaseJob instance.
     * @param $queue
     * @param $job
     * @return ExtendedDatabaseJob
     * @throws Throwable
     */
    protected function marshalJob($queue, $job)
    {
        $job = $this->markJobAsReserved($job);

        $this->database->commit();

        return new ExtendedDatabaseJob(
            $this->container, $this, $job, $this->connectionName, $queue
        );
    }

    /**
     * @param $job
     * @return DatabaseJobRecord
     */
    protected function markJobAsReserved($job)
    {
        $this->database->table($this->table)->where('id', $job->id)->update([
            'reserved_at' => Carbon::now(),
            'attempts' => $job->increment(),
        ]);

        return $job;
    }

    /** Push a raw payload onto the queue.
     *
     * @param $payload
     * @param $queue
     * @param array $options
     * @return int|mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return $this->pushToDatabase($queue, $payload, 0, 0, $options['user_id'] ?? null, $options['type_id'] ?? null);
    }

    /**
     * Release a reserved job back onto the queue.
     * @param $queue
     * @param $job
     * @param $delay
     * @return int|mixed
     */
    public function release($queue, $job, $delay)
    {
        return $this->pushToDatabase($queue, $job->payload, $delay, $job->attempts, $job->user_id, $job->type_id);
    }

    /**
     * @param $queue
     * @param $payload
     * @param $delay
     * @param $attempts
     * @param $userId
     * @param $typeId
     * @return int|mixed
     */
    protected function pushToDatabase($queue, $payload, $delay = 0, $attempts = 0, $userId = null, $typeId = null)
    {
        return $this->database->table($this->table)->insertGetId($this->buildDatabaseRecord(
            $this->getQueue($queue), $payload, Carbon::parse($this->availableAt($delay)), $attempts, $userId, $typeId
        ));
    }

    /**
     * @param $delay
     * @return Carbon|DateTimeInterface
     */
    protected function availableAt($delay = 0)
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof DateTimeInterface
            ? $delay
            : Carbon::now()->addSecond();
    }

    /**
     * @param $delay
     * @return Carbon|DateInterval|DateTimeInterface|int|mixed
     */
    protected function parseDateInterval($delay)
    {
        if ($delay instanceof DateInterval) {
            $delay = Carbon::now()->add($delay);
        }

        return $delay;
    }

    /**
     * Create an array to insert for the given job.
     * @param $queue
     * @param $payload
     * @param $availableAt
     * @param $attempts
     * @param $userId
     * @param $typeId
     * @return array
     */
    protected function buildDatabaseRecord($queue, $payload, $availableAt, $attempts = 0, $userId = null, $typeId = null)
    {
        return [
            'queue' => $queue,
            'attempts' => $attempts,
            'reserved_at' => null,
            'available_at' => $availableAt,
            'created_at' => Carbon::now(),
            'payload' => $payload,
            'user_id' => $userId,
            'type_id' => $typeId,
        ];
    }
}
