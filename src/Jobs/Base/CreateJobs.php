<?php

namespace Common\Jobs\Base;

use Carbon\Carbon;
use Common\Helpers\LoggerHelper;
use Common\Jobs\LogJob\LogJobParser;
use Common\Models\BaseModel;
use Exception;
use Queue;
use Cache;
use Illuminate\Support\Str;
use Random\RandomException;

class CreateJobs
{
    /**
     * Содержит в себе все переданные параметры очереди
     *
     * @var array
     */
    protected static array $data = [];

    /**
     * Обозначение приоритета/названия очереди
     *
     * @var string
     */
    protected static string $priority = '';

    /**
     * Путь к файлу, если он требуется (Обычно передается путь до Атон файлов)
     *
     * @var string
     */
    protected static string $path = '';

    /**
     * Айдишник пользователя
     *
     * @var int
     */
    protected static int $userId;

    public const PREFIX_ONLINE = 'last_online.';

    /**
     * Обработка и проверка очереди
     *
     * @param $jobClass
     *
     * @return false|mixed
     * @throws RandomException
     */
    public static function addQueue($jobClass)
    {
        $date = Carbon::now()->subMinute()->format('Y-m-d H:i:s');

        //Если первое значение не число, скорее всего переделан не айдишник пользователя
        //В таком случа нам незачем проверять дальше в кэше, но что бы не добавлять много лишний логики
        //просто рандомное значение запишем
        if (!is_numeric(self::$data[0])) {
            self::$userId = random_int(1, 10000);
        } else {
            [self::$userId] = self::$data;
        }

        //Для юзеров которые онлайн делаем отдельную очередь, что бы они не ждали
        //В middleware в кэш записывает время онлайна пользователя. Если оно будет больше текущего с минус минутой, значит он онлайн (по край не мере был в течении минуты)
        if (config()->has('create-jobs.parse_jobs') && in_array($jobClass, config('create-jobs.parse_jobs')) &&
            Cache::tags([config('cache.tags')])->has(self::PREFIX_ONLINE . self::$userId) &&
            Cache::tags([config('cache.tags')])->get(self::PREFIX_ONLINE . self::$userId) > $date) {
            self::$priority = 'high-online';
        }

        return self::push($jobClass);
    }

    /**
     * Отправка джобы в очередь
     *
     * @param $jobClass
     *
     * @return false|mixed
     */
    public static function push($jobClass)
    {
        //Очистим все ключи перед добавлением ключа кэша, тк он обязателен
        $data = array_values(self::$data);
        //Тк перешли на реббит, теперь не можем отслеживать сообщения в очереди.
        //Будем создавать кэш и проверять что бы не создать дубли
        $data['cache_key'] = 'queue_' . self::$priority . '_' . self::$userId . '_' . $jobClass::TYPE;

        //внутри добавлена проверка кэша
        $queue = Queue::push($jobClass, $data, self::$priority);

        if ($queue) {
            self::createLogParse(self::$priority, $jobClass, self::$userId, self::$path);
            return $queue;
        }

        return false;
    }

    /**
     * Очередь типа парсер
     *
     * @param $jobClass
     * @param $data
     * @param string $path
     *
     * @return false|null
     */
    public static function parse($jobClass, $data, string $path = ''): ?bool
    {
        self::$data = $data;
        self::$priority = 'parse';
        self::$path = $path;
        return self::checkTypeJob($jobClass);
    }

    /**
     * Очередь с обычным приоритетом
     *
     * @param $jobClass
     * @param $data
     *
     * @return false|null
     */
    public static function default($jobClass, $data): ?bool
    {
        self::$data = $data;
        self::$priority = 'default';
        return self::checkTypeJob($jobClass);
    }

    /**
     * Очередь для чата 
     *
     * @param $jobClass
     * @param $data
     *
     * @return false|null
     */
    public static function messages($jobClass, $data): ?bool
    {
        self::$data = $data;
        self::$priority = 'messages';
        return self::checkTypeJob($jobClass);
    }

    /**
     * Очередь для CRM связанная с Атон
     *
     * @param $jobClass
     * @param $data
     *
     * @return false|null
     */
    public static function crmAton($jobClass, $data): ?bool
    {
        self::$data = $data;
        self::$priority = 'crm-aton';
        return self::checkTypeJob($jobClass);
    }

    /**
     * Очередь для Атон
     *
     * @param $jobClass
     * @param $data
     *
     * @return false|null
     */
    public static function aton($jobClass, $data): ?bool
    {
        self::$data = $data;
        self::$priority = 'crm-aton';
        return self::checkTypeJob($jobClass);
    }

    /**
     * Очередь с высоким приоритетом
     *
     * @param $jobClass
     * @param $data
     *
     * @return false|null
     */
    public static function high($jobClass, $data): ?bool
    {
        self::$data = $data;
        self::$priority = 'high';
        return self::checkTypeJob($jobClass);
    }

    /**
     * Проверяем, что у джобы проставлен ее тип
     *
     * @param $jobClass
     *
     * @return false|mixed
     */
    public static function checkTypeJob($jobClass)
    {
        if ($jobClass::TYPE === 0) {
            LoggerHelper::getLogger('add-queue-' . self::$priority)
                ->error('Для класса такой очереди не определен тип (' . $jobClass . ')');
            return false;
        }

        return self::addQueue($jobClass);
    }

    /**
     * Создание лога парсера
     * что бы записывать время когда пользователь создаст джобу на парс файла или токена брокера
     *
     * @param $priority
     * @param $jobClass
     * @param $userId
     * @param $filePath
     *
     * @return BaseModel|false
     */
    public static function createLogParse($priority, $jobClass, $userId, $filePath = null)
    {
        if ($priority === 'parse' || (config()->has('create-jobs.parse_jobs') && in_array(
                    $jobClass,
                    config('create-jobs.parse_jobs'),
                ))) {
            try {
                return LogJobParser::create([
                    'user_id' => $userId,
                    'job_name' => $jobClass,
                    'path_file' => $filePath,
                ]);
            } catch (Exception $e) {
                LoggerHelper::getLogger('Add-queue->create-log-parser')->error($e);
            }
        }

        return false;
    }
}