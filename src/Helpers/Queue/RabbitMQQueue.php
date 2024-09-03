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
    public function push($job, $data = '', $queue = null)
    {
        if ($this->isImmediateJob($job)) {
            return $this->pushRaw($this->createPayload($job, $queue, $data), $queue);
        }

        if ($this->isInvalidData($data)) {
            return false;
        }

        Cache::tags(['job'])->add($data['options']['cache_key'], true, 1440);

        return $this->pushRaw($this->createPayload($job, $queue, $data), $queue);
    }

    /**
     * Проверьте, является ли задание экземпляром SendQueuedMailable (Отправка письма на почту),
     * является ли задание экземпляром BroadcastEvent (вебсокета для процентовки выполнения очереди),
     * является ли задание экземпляром MakeSearchable (Laravel scout)
     * или задача принадлежит к сервису Каталога (где не надо проверять на кэш)
     *
     * @param mixed $job
     *
     * @return bool
     */
    protected function isImmediateJob($job): bool
    {
        $path = str_replace('\\', '/', $job);
        return in_array(class_basename($job), ['SendQueuedMailable', 'BroadcastEvent', 'MakeSearchable'])
            ||
            str_contains($path, '/Catalog/');
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
            LoggerHelper::getLogger(class_basename($this) . '-' . __FUNCTION__)
                ->error('Значение очереди, должно быть массивом', [$data]);
            return true;
        }

        if (!isset($data['options']['cache_key'])) {
            LoggerHelper::getLogger(class_basename($this) . '-' . __FUNCTION__)
                ->error('В значении очереди не определен ключ для кэширования', $data);
            return true;
        }

        if (isset($data['options']['cache_check']) && !$data['options']['cache_check']) {
            return false;
        }

        if (Cache::tags(['job'])->has($data['options']['cache_key'])) {
//            LoggerHelper::getLogger(class_basename($this) . '-' . __FUNCTION__)->info('Такой ключ уже существует', [$data]);
            return true;
        }

        return false;
    }
}
