<?php

namespace Common\Helpers\Queue;

use Cache;
use Common\Helpers\LoggerHelper;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue as BaseQueue;

class RabbitMQQueue extends BaseQueue
{
    /**
     * Поместите задание в очередь.
     *
     * @param mixed $job Работа, которую нужно продвигать.
     * @param mixed $data Данные, связанные с заданием.
     * @param string|null $queue Имя очереди.
     *
     * @return mixed Результат помещения задания в очередь.
     *
     * @throws AMQPProtocolChannelException Если возникла ошибка канала протокола AMQP.
     */
    public function push($job, $data = '', $queue = null)
    {
        // Проверьте, является ли задание экземпляром SendQueuedMailable (Отправка письма на почту).
        // Или является ли задание экземпляром BroadcastEvent (вебсокета для процентовки выполнения очереди).
        if (class_basename($job) === 'SendQueuedMailable' || class_basename($job) === 'BroadcastEvent') {
            // Создайте полезную нагрузку и поместите ее в очередь.
            return $this->pushRaw($this->createPayload($job, $queue, $data), $queue);
        }

        // Проверьте, являются ли данные массивом, присутствует ли в данных ключ кэша,
        // или если ключ кеша уже присутствует в кеше
        if ($this->checkArrayData($data) || $this->checkCacheKeyInData($data) || Cache::get($data['cache_key'])) {
            // Верните false, чтобы указать, что задание не следует помещать в очередь.
            return false;
        }

        // Добавить ключ кеша в кеш со сроком действия 1440 минут (1 день)
        Cache::add($data['cache_key'], true, 1440);

        // Создайте полезную нагрузку и поместите ее в очередь.
        return $this->pushRaw($this->createPayload($job, $queue, $data), $queue);
    }

    /**
     * Проверьте, являются ли данные массивом.
     *
     * @param mixed $data Данные, которые необходимо проверить.
     *
     * @return bool True, если данные представляют собой массив, в противном случае — false.
     */
    private function checkArrayData($data): bool
    {
        // Проверьте, являются ли данные массивом
        if (!is_array($data)) {
            // Зарегистрируйте ошибку и верните true, чтобы указать, что данные не являются массивом.
            LoggerHelper::getLogger(class_basename($this) . '-' . __FUNCTION__)->error('Значение очереди, должно быть массивом', [$data]);
            return true;
        }

        // Верните false, чтобы указать, что данные представляют собой массив.
        return false;
    }

    /**
     * Проверьте, присутствует ли в данных ключ кэша.
     *
     * @param mixed $data Данные, которые необходимо проверить.
     *
     * @return bool True, если ключ кэша присутствует в данных, в противном случае — false.
     */
    private function checkCacheKeyInData($data): bool
    {
        // Проверьте, присутствует ли ключ кэша в данных
        if (!isset($data['cache_key'])) {
            // Зарегистрируйте ошибку и верните true, чтобы указать, что ключ кэша отсутствует в данных.
            LoggerHelper::getLogger(class_basename($this) . '-' . __FUNCTION__)->error('В значении очереди не определен ключ для кэширования', [$data]);
            return true;
        }

        // Верните false, чтобы указать, что ключ кэша присутствует в данных.
        return false;
    }
}