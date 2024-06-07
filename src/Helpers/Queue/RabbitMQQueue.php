<?php

namespace Common\Helpers\Queue;

use Cache;
use Common\Helpers\LoggerHelper;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue as BaseQueue;

class RabbitMQQueue extends BaseQueue
{
    /**
     * Помещает задание в очередь.
     *
     * @param mixed $job
     * @param mixed $data
     * @param string|null $queue
     * @return mixed
     * @throws AMQPProtocolChannelException
     */
    public function push($job, $data = '', string $queue = null)
    {
        if ($this->isImmediateJob($job)) {
            return $this->pushRaw($this->createPayload($job, $queue, $data), $queue);
        }

        if ($this->isInvalidData($data)) {
            return false;
        }

        Cache::add($data['cache_key'], true, 1440);

        return $this->pushRaw($this->createPayload($job, $queue, $data), $queue);
    }

    /**
     * Проверьте, является ли задание экземпляром SendQueuedMailable (Отправка письма на почту).
     * Или является ли задание экземпляром BroadcastEvent (вебсокета для процентовки выполнения очереди).
     *
     * @param mixed $job
     *
     * @return bool
     */
    protected function isImmediateJob($job): bool
    {
        return in_array(class_basename($job), ['SendQueuedMailable', 'BroadcastEvent']);
    }

    /**
     * Проверяет данные на корректность.
     *
     * @param mixed $data
     * @return bool
     */
    protected function isInvalidData($data): bool
    {
        if (!is_array($data)) {
            $this->logError(__FUNCTION__, 'Значение очереди, должно быть массивом', $data);
            return true;
        }

        if (!isset($data['cache_key'])) {
            $this->logError(__FUNCTION__, 'В значении очереди не определен ключ для кэширования', $data);
            return true;
        }

        if (Cache::has($data['cache_key'])) {
            $this->logError(__FUNCTION__, 'Такой ключ уже существует', $data);
            return true;
        }

        return false;
    }

    /**
     * Логирует ошибки.
     *
     * @param string $method
     * @param string $message
     * @param mixed $data
     */
    protected function logError(string $method, string $message, $data): void
    {
        LoggerHelper::getLogger(class_basename($this) . '-' . $method)->error($message, [$data]);
    }
}
