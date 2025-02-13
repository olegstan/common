<?php
namespace Common\Helpers;

use App\Jobs\Tinkoff\TinkoffJob;
use Common\Jobs\Base\CreateJobs;
use Common\Jobs\SendToLokiJob;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Queue;

class LokiLogger implements LoggerInterface
{
    /**
     * @var array
     */
    private static array $buffer = [];
    /**
     * @var array
     */
    private static array $loggers = [];
    /**
     * @var int
     */
    private static int $batchSize = 100;

    /**
     *
     */
    public static function flushListeners()
    {
        self::$loggers = [];
    }

    /**
     * @param $message
     * @param string $app
     */
    public static function debug($message, $app = 'laravel')
    {
        self::log($message, $app, 'debug');
    }
    /**
     * @param $message
     * @param string $app
     */
    public static function info($message, $app = 'laravel')
    {
        self::log($message, $app, 'info');
    }

    /**
     * @param $message
     * @param string $app
     */
    public static function error($message, $app = 'laravel')
    {
        self::log($message, $app, 'error');
    }

    /**
     * @param $data
     * @return array
     */
    public static function filterSecureData(&$data)
    {
        // Если данные — массив, обрабатываем каждый элемент рекурсивно
        if (is_array($data)) {
            if(isset($data['sql']))
            {
                $data['sql'] = SqlLogSanitizer::sanitize($data['sql']);
            }
            return $data;
        }

        return $data;
    }

    /**
     * @param $key
     */
    public static function getLogger($key)
    {
        if(!isset(self::$loggers[$key]))
        {
            if(LoggerHelper::$commandKey)
            {
                //определяем путь куда писать логи
                switch (LoggerHelper::$commandKey)
                {
                    case 'queue':
                        $type = 'queue';
                        $path = LoggerHelper::prepareCommandKey(LoggerHelper::$jobKey);
                        break;
                    case 'tests':
                        $type = 'tests';
                        $path = LoggerHelper::prepareCommandKey(LoggerHelper::$testKey);
                        break;
                    case 'commands':
                    default:
                        $type = 'commands';
                        $path = LoggerHelper::prepareCommandKey(LoggerHelper::$commandKey);
                        break;
                }
            }else{
                $type = 'front';

                $url = Request::fullUrl();

                if(str_contains($url, 'api/v1/call'))
                {
                    $partsBeforeGetParams = explode( '?', $url);
                    $parts = explode( '/', $partsBeforeGetParams[0] ?? '');
                    $partsLength = count($parts);

                    if(isset($parts[$partsLength - 1]) && isset($parts[$partsLength - 2]))
                    {
                        $controllerName = $parts[$partsLength - 2];
                        $methodName = $parts[$partsLength - 1];

                        $path = $controllerName . '/' . $methodName;
                    }
                }else{
                    $path = 'common';
                }
            }

            self::$loggers[$key] = ["app" => config('app.name'), "env" => config('app.env'), "type" =>  $type, "path" => $path, "key" => $key];
        }

        return self::$loggers[$key];
    }

    /**
     * @param $message
     * @param string $key
     * @param string $level
     */
    public static function log($message, $key = 'laravel', string $level = 'info')
    {
        $formattedMessage = str_replace(PHP_EOL, "\n", $message);

        $formattedMessage = self::filterSecureData($formattedMessage);

        $timestamp = (int) floor(microtime(true) * 1e9);

        // Превращаем в строку
        $timestampString = (string) $timestamp;

        $loggerStreams = self::getLogger($key);

        self::$buffer[] = [
            "stream" => $loggerStreams + ['level' => $level],
            "values" => [[$timestampString, json_encode($formattedMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]]
        ];

        if (count(self::$buffer) >= self::$batchSize) {
            self::flush();
        }
    }

    /**
     *
     */
    public static function flush()
    {
        if (empty(self::$buffer)) {
            return;
        }

        // Копируем текущий буфер и очищаем
        $logsToSend = self::$buffer;
        self::$buffer = [];

        CreateJobs::createNotUniq(
            SendToLokiJob::class,
            $logsToSend,
            'loki',
        );
    }

    /**
     * Сохраняет логи в файл как альтернативный вариант.
     */
    private static function saveToFallbackFile()
    {
        $filePath = storage_path('logs/failed_loki_logs.json');
        File::append($filePath, json_encode(self::$buffer, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);
        self::$buffer = []; // Очищаем буфер после сохранения
    }

    /**
     * Повторная попытка отправки неудачных логов.
     */
    private static function retryFailedLogs()
    {
        // Можно добавить логику для повторной отправки через некоторое время
        Queue::later(now()->addMinutes(5), function () {
            self::flush(); // Попробовать отправить снова
        });
    }
}