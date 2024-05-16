<?php

namespace Common\Jobs\Base;

use App\Helpers\BrokerParsers\Aton\AtonParser;
use Common\Helpers\LoggerHelper;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob;

class BaseRabbitMQJob extends RabbitMQJob
{
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
        parent::fail();

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