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
            // Устанавливаем командную клавишу для ведения журнала
            LoggerHelper::$commandKey = 'queue';

            $payload = $message->getBody();

            // Проверяем, не пуста ли полезная нагрузка
            if ($payload) {
                $jsonDecoded = json_decode($payload, true);

                // Проверяем, установлен ли ключ 'job' в декодированном JSON
                if (isset($jsonDecoded['job'])) {
                    $path = explode('\\', $jsonDecoded['job']);
                    $count = count($path);

                    // Проверяем, существует ли последний элемент пути
                    if (isset($path[$count - 1])) {
                        // Устанавливаем ключ задания для регистрации
                        LoggerHelper::$jobKey = strtolower($path[$count - 1]);
                    }
                }
            }

            // Прослушиваем запросы к базе данных и записываем их при необходимости
            DB::listen(function ($sql) {
                $key = $sql->time > 100 ? 'slow-query' : 'query';
                $sqlWithBindings = $sql->sql;

                if (LoggerHelper::$logQuery || $sql->time > 100) {
                    foreach ($sql->bindings as $binding) {
                        $value = is_numeric($binding) ? $binding : "'" . $binding . "'";
                        $sqlWithBindings = preg_replace('/\?/', $value, $sqlWithBindings, 1);
                    }

                    LoggerHelper::getLogger($key)->debug(
                        'SQL => ' . $sqlWithBindings . PHP_EOL .
                        'TIME => ' . $sql->time . ' milliseconds' . PHP_EOL,
                    );
                }
            });
        }
    }

    /**
     * @return void
     * @throws BindingResolutionException
     */
    public function delete(): void
    {
        parent::delete();

        $data = json_decode($this->getRawBody());

        if ((is_array($data) && isset($data['cache_key'])) || (is_object($data) && isset($data->cache_key))) {
            Cache::forget($data['cache_key']);
        }

        //Возвращаем статическим переменным их дефолтные значения
        foreach (BaseJob::$allStaticValues as $path => $statics) {
            try {
                $class = new $path();

                foreach ($statics as $key => $value) {
                    $class::$$key = $value;
                }
            } catch (Exception $e) {
                LoggerHelper::getLogger('job-delete')->error($e);
            }
        }
    }

    /**
     * @param $e
     *
     * @return void
     */
    public function fail($e = null): void
    {
        parent::fail($e);

        $data = json_decode($this->getRawBody());

        if ((is_array($data) && isset($data['cache_key'])) || (is_object($data) && isset($data->cache_key))) {
            Cache::forget($data['cache_key']);
        }

        //Возвращаем статическим переменным их дефолтные значения
        foreach (BaseJob::$allStaticValues as $path => $statics) {
            try {
                $class = new $path();

                foreach ($statics as $key => $value) {
                    $class::$$key = $value;
                }
            } catch (Exception $e) {
                LoggerHelper::getLogger('job-fail')->error($e);
            }
        }
    }
}