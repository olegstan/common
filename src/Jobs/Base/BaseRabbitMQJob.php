<?php

namespace Common\Jobs\Base;

use App\Helpers\BrokerParsers\Aton\AtonParser;
use Common\Helpers\LoggerHelper;
use Common\Helpers\Queue\RabbitMQQueue;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use DB;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob;
use PhpAmqpLib\Message\AMQPMessage;

class BaseRabbitMQJob extends RabbitMQJob
{
    public function __construct(Container $container,
        RabbitMQQueue $rabbitmq,
        AMQPMessage $message,
        string $connectionName,
        string $queue)
    {
        if (config('app.extended_log')) {
            LoggerHelper::$commandKey = 'queue';

            $payload = $message->payload;

            if ($payload) {
                $jsonDecoded = json_decode($message->payload, true);

                if (isset($jsonDecoded['job'])) {
                    $path = explode('\\', $jsonDecoded['job']);
                    $count = count($path);

                    if (isset($path[$count - 1])) {
                        LoggerHelper::$jobKey = strtolower($path[$count - 1]);
                    }
                }
            }

            DB::listen(function ($sql) {
                /**
                 *
                 */
                $key = $sql->time > 100 ? 'slow-query' : 'query';
                $sqlWithBindings = $sql->sql;

                if (LoggerHelper::$logQuery || $sql->time > 100) {
                    foreach ($sql->bindings as $binding) {
                        $value = is_numeric($binding) ? $binding : "'" . $binding . "'";
                        $sqlWithBindings = preg_replace('/\?/', $value, $sqlWithBindings, 1);
                    }

                    LoggerHelper::getLogger($key)->debug(
                        'SQL => ' . $sqlWithBindings . PHP_EOL .
                        'TIME => ' . $sql->time . ' milliseconds' . PHP_EOL
                    );
                }
            });
        }

        $this->container = $container;
        $this->rabbitmq = $rabbitmq;
        $this->message = $message;
        $this->connectionName = $connectionName;
        $this->queue = $queue;
        $this->decoded = $this->payload();
    }

    /**
     * @return void
     * @throws BindingResolutionException
     */
    public function delete(): void
    {
        parent::delete();

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