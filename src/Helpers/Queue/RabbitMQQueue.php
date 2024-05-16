<?php

namespace Common\Helpers\Queue;

use Cache;
use Common\Helpers\LoggerHelper;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue as BaseQueue;

class RabbitMQQueue extends BaseQueue
{
    /**
     * @param $job
     * @param $data
     * @param $queue
     *
     * @return mixed
     * @throws AMQPProtocolChannelException
     */
    public function push($job, $data = '', $queue = null)
    {
        if ($this->checkArrayData($data) || $this->checkCacheKeyInData($data)) {
            return false;
        }

        if (Cache::get($data['cache_key'])) {
            return false;
        }

        //Сохраним в кэш, что бы отслеживать сообщения и не дублировать их
        //1440 - минут в сутках
        Cache::add($data['cache_key'], true, 1440);
        return $this->pushRaw($this->createPayload($job, $queue, $data), $queue);
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function checkArrayData($data): bool
    {
        if (!is_array($data)) {
            LoggerHelper::getLogger(class_basename($this) . '-' . __FUNCTION__)->error('Значение очереди, должно быть массивом', [$data]);
            return true;
        }

        return false;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function checkCacheKeyInData($data): bool
    {
        if (!isset($data['cache_key'])) {
            LoggerHelper::getLogger(class_basename($this) . '-' . __FUNCTION__)->error('В значении очереди не определен ключ для кэширования', [$data]);
            return true;
        }

        return false;
    }
}