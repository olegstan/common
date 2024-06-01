<?php

namespace Common\Jobs\Traits\CreateJobs;

use Carbon\Carbon;
use Common\Jobs\Base\CreateJobs;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

trait CreateJobsSetTrait
{
    /**
     * Установите класс джобы.
     *
     * @param string $jobClass Класс джобы.
     *
     * @return CreateJobs
     */
    public function setJobClass(string $jobClass): CreateJobs
    {
        $this->job_class = $jobClass;
        return $this;
    }

    /**
     * Установите данные джобы.
     *
     * @param $data //Данные.
     *
     * @return CreateJobs
     */
    public function setData($data): CreateJobs
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Установите идентификатор пользователя.
     *
     * @param $data //Данные.
     *
     * @return CreateJobs
     */
    public function setUserId($data): CreateJobs
    {
        // Проверяем, существует ли ключ 'user_id' в данных джобы
        if (is_array($data) && array_key_exists('user_id', $data)) {
            $userId = $data['user_id'];
        } elseif (is_array($data) && is_numeric($data[0])) {
            // Если ключ user_id не существует, проверьте, является ли первое значение числовым.
            // По старой логике первое значение всегда было идентификатором пользователя
            $userId = $data[0];
        } elseif (is_numeric($data)) {
            $userId = $data;
        } else {
            // Если ни ключ user_id, ни первое значение не являются числовыми, сгенерируйте случайный идентификатор пользователя.
            $userId = rand(1, 10000);
        }

        $this->user_id = $userId;
        return $this;
    }

    /**
     * Установите приоритет / навзание очереди.
     *
     * @param string $priority Приоритет.
     *
     * @return CreateJobs
     */
    public function setPriority(string $priority): CreateJobs
    {
        $date = Carbon::now()->subMinute()->format('Y-m-d H:i:s');
        $cache = Cache::tags([config('cache.tags')]);
        $cacheKey = self::PREFIX_ONLINE . $this->getUserId();
        $configHas = config()->has('create-jobs.parse_jobs');

        // Проверьте, находится ли пользователь в сети, и установите соответствующий приоритет.
        if ($configHas && in_array($this->getJobClass(), config('create-jobs.parse_jobs'))
            && $cache->has($cacheKey,) && $cache->get($cacheKey) > $date) {
            $priority = 'high-online';
        }

        $this->priority = $priority;
        return $this;
    }

    /**
     * Установите путь до файла.
     *
     * @param $data //Данные.
     *
     * @return CreateJobs
     */
    public function setPath($data): CreateJobs
    {
        $path = '';
        if (array_key_exists('path', $data)) {
            $path = $data['path'];
        }

        $this->path = $path;
        return $this;
    }

    /**
     * Установите UUID.
     *
     * @return CreateJobs
     */
    public function setUuid(): CreateJobs
    {
        $this->uuid = Str::uuid();
        return $this;
    }

    /**
     * Установите ключ кэша для очереди.
     *
     * @return CreateJobs
     */
    public function setCacheKeyQueue(): CreateJobs
    {
        $this->cache_key_queue = 'queue_' . $this->getPriority() .
            '_' . $this->getUserId() .
            '_' . $this->getJobClass()::TYPE;

        return $this;
    }

    /**
     * Задайте имя подключения.
     *
     * @param string $connectionName Имя соединения.
     *
     * @return CreateJobs
     */
    public function setConnectionName(string $connectionName): CreateJobs
    {
        $this->connection_name = $connectionName;

        if (empty($connectionName)) {
            $this->connection_name = config('queue.default');
        }

        return $this;
    }
}