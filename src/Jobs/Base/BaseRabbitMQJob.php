<?php

namespace Common\Jobs\Base;

use Common\Helpers\LoggerHelper;
use Common\Helpers\Queue\RabbitMQQueue;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use DB;
use Cache;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob;
use PhpAmqpLib\Message\AMQPMessage;

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
        DB::listen(static function ($sql) {
            $key = $sql->time > 100 ? 'slow-query' : 'query';
            $sqlWithBindings = $sql->sql;

            if (LoggerHelper::$logQuery || $sql->time > 100) {
                foreach ($sql->bindings as $binding) {
                    $value = is_numeric($binding) ? $binding : "'{$binding}'";
                    $sqlWithBindings = preg_replace('/\?/', $value, $sqlWithBindings, 1);
                }

                LoggerHelper::getLogger($key)->debug(
                    "SQL => {$sqlWithBindings}" . PHP_EOL .
                    "TIME => {$sql->time} milliseconds" . PHP_EOL
                );
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
}
