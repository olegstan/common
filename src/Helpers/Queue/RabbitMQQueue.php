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
     * @param mixed $job  Задача, которая отправляется в очередь
     * @param mixed $data  Данные для задачи
     * @param null $queue  Имя очереди (по умолчанию - null)
     *
     * @return mixed  Возвращает UUID задания или false в случае ошибки
     * @throws AMQPProtocolChannelException
     */
    public function push($job, $data = '', $queue = null)
    {
        // Если задача должна быть выполнена немедленно, отправляем её напрямую
        if ($this->isImmediateJob($job)) {
            return $this->pushRawJob($job, $queue, $data);
        }

        // Если данные некорректны, логируем ошибку и не выполняем задачу
        if ($this->isInvalidData($data)) {
            return false;
        }

        // Кэшируем задачу, если она еще не была кэширована
        $this->cacheJobData($data);

        // Отправляем задачу в очередь
        return $this->pushRawJob($job, $queue, $data);
    }

    /**
     * Отправляет задание в очередь напрямую.
     *
     * @param mixed $job  Задача, которая отправляется
     * @param string|null $queue  Очередь
     * @param mixed $data  Данные для задачи
     *
     * @return mixed
     */
    protected function pushRawJob($job, ?string $queue, $data)
    {
        return $this->pushRaw($this->createPayload($job, $queue, $data), $queue);
    }

    /**
     * Проверьте, является ли задание экземпляром SendQueuedMailable (Отправка письма на почту),
     * является ли задание экземпляром BroadcastEvent (вебсокета для процентовки выполнения очереди),
     * является ли задание экземпляром MakeSearchable (Laravel scout)
     * или задача принадлежит к сервису Каталога (где не надо проверять на кэш)
     *
     * @param mixed $job Задача
     *
     * @return bool  Возвращает true, если задача немедленная (без кэширования)
     */
    protected function isImmediateJob($job): bool
    {
        if (is_object($job)) {
            return in_array(class_basename($job), ['SendQueuedMailable', 'BroadcastEvent', 'MakeSearchable']);
        }

        $path = str_replace('\\', '/', $job);
        return str_contains($path, '/Catalog/');
    }

    /**
     * Проверяет данные на корректность.
     *
     * @param mixed $data Данные задачи
     *
     * @return bool  Возвращает true, если данные некорректны
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

        // Проверяем, не существует ли задача с таким ключом в кэше
        if ($this->isCachedJob($data)) {
            return true;
        }

        return false;
    }

    /**
     * Проверяет, находится ли задача уже в кэше.
     *
     * @param mixed $data  Данные задачи
     *
     * @return bool  Возвращает true, если задача есть в кэше
     */
    protected function isCachedJob($data): bool
    {
        return Cache::tags(['job'])->has($data['options']['cache_key']);
    }

    /**
     * Кэширует задачу в Redis на 24 часа.
     *
     * @param mixed $data  Данные задачи
     *
     * @return void
     */
    protected function cacheJobData($data): void
    {
        Cache::tags(['job'])->add($data['options']['cache_key'], true, 1440);
    }
}
