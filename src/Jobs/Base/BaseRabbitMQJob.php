<?php

namespace Common\Jobs\Base;

use Cache;
use Carbon\Carbon;
use Common\Helpers\LoggerHelper;
use Common\Helpers\Queue\RabbitMQQueue;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PhpAmqpLib\Message\AMQPMessage;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob;

class BaseRabbitMQJob extends RabbitMQJob
{
    /**
     * Конструктор класса BaseRabbitMQJob.
     *
     * @param Container $container Экземпляр контейнера.
     * @param RabbitMQQueue $rabbitmq Экземпляр RabbitMQQueue.
     * @param AMQPMessage $message Экземпляр AMQPMessage.
     * @param string $connectionName Имя соединения.
     * @param string $queue Имя очереди.
     */
    public function __construct($container, $rabbitmq, $message, $connectionName, $queue)
    {
        parent::__construct($container, $rabbitmq, $message, $connectionName, $queue);

        // Проверяем, включено ли расширенное ведение журнала
        if (config('app.extended_log')) {
            $this->setupLogging($message);
            $this->listenToDatabaseQueries();
        }
    }

    /**
     * Настраивает ведение журнала для задания.
     *
     * @param AMQPMessage $message Экземпляр AMQPMessage.
     */
    protected function setupLogging($message): void
    {
        LoggerHelper::$commandKey = 'queue';
        LoggerHelper::flushListeners();

        $payload = $message->getBody();

        if ($payload) {
            $jsonDecoded = json_decode($payload, true);
            if (isset($jsonDecoded['job'])) {
                $path = explode('\\', $jsonDecoded['job']);
                $jobName = strtolower(end($path));
                LoggerHelper::$jobKey = $jobName;
            }
        }
    }

    /**
     * Слушает запросы к базе данных и записывает их при необходимости.
     */
    protected function listenToDatabaseQueries(): void
    {
        Event::forget(QueryExecuted::class);
        DB::listen(function ($sql)
        {
            if (LoggerHelper::$logQuery || $sql->time > 100)
            {
                LoggerHelper::listenQuery($sql);
            }
        });
    }

    /**
     * Удаляет задание из очереди и очищает кэш.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function delete(): void
    {
        parent::delete();
        $this->clearCache();
    }

    /**
     * Обрабатывает ошибку и очищает кэш.
     *
     * @param mixed $e
     *
     * @return void
     */
    public function fail($e = null): void
    {
        parent::fail($e);
        $this->clearCache();
    }

    /**
     * Очищает кэш на основе ключа из данных задания.
     */
    protected function clearCache(): void
    {
        $data = json_decode($this->getRawBody(), true);
        if (isset($data['data']['options']['cache_key'])) {
            Cache::tags(['job'])->forget($data['data']['options']['cache_key']);
        }
    }

    /**
     * Возвращает опции надстроек джобы
     *
     * @return object
     */
    public function getOptions(): object
    {
        $json = json_decode($this->getRawBody());
        return $json->data->options;
    }

    /**
     * Возвращает тип джобы
     *
     * @return int
     */
    public function getOptionsType(): int
    {
        return $this->getOptions()->job_type;
    }

    /**
     * Возвращает uuid джобы
     *
     * @return string
     */
    public function getOptionsUuid(): string
    {
        return $this->getOptions()->uuid;
    }

    /**
     * Возвращает дату и время создания джобы
     *
     * @return Carbon
     */
    public function getOptionsCreateAt(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->getOptions()->create_at);
    }

    /**
     * Возвращает ключ кэша джобы
     *
     * @return string
     */
    public function getOptionsCacheKey(): string
    {
        return $this->getOptions()->cache_key;
    }

    /**
     * Возвращает булево на проверку кэширования
     *
     * @return bool
     */
    public function getOptionsCacheCheck(): bool
    {
        return $this->getOptions()->cache_check;
    }
}
