<?php

namespace Common\Jobs\Traits\CreateJobs;

trait CreateJobsGetTrait
{
    /**
     * Получите класс джобы.
     *
     * @return string Класс джобы.
     */
    public function getJobClass(): string
    {
        return $this->job_class;
    }

    /**
     * Получите данные джобы.
     *
     * @return mixed The data.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Получите идентификатор пользователя.
     *
     * @return int Идентификатор пользователя.
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * Получите приоритет / название очереди.
     *
     * @return string Приоритет.
     */
    public function getPriority(): string
    {
        return $this->priority;
    }

    /**
     * Получите путь до файла.
     *
     * @return string Путь.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Получите UUID.
     *
     * @return string UUID.
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * Получите ключ кэша для очереди.
     *
     * @return string Ключ кэша для очереди.
     */
    public function getCacheKeyQueue(): string
    {
        return $this->cache_key_queue;
    }

    /**
     * Получите название подключения очереди.
     *
     * @return string Название подключения.
     */
    public function getConnectionName(): string
    {
        return $this->connection_name;
    }
}