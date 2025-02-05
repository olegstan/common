<?php
namespace Common\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Request;

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

            self::$loggers[$key] = ["app" => config('app.name'), "type" =>  $type, "path" => $path];
        }else{
            return self::$loggers[$key];
        }
    }

    /**
     * @param $message
     * @param string $key
     * @param string $level
     */
    public static function log($message, $key = 'laravel', string $level = 'info')
    {
        $formattedMessage = str_replace(PHP_EOL, "\n", $message);

        $timestamp = (int) floor(microtime(true) * 1e9);

        // Превращаем в строку
        $timestampString = (string) $timestamp;

        $loggerStreams = self::getLogger($key);

        self::$buffer[] = [
            "stream" => [...$loggerStreams, ...['level' => $level]],
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
        if (empty(self::$buffer)) return;

        try {
            $response = Http::post('http://localhost:3100/loki/api/v1/push', [
                'streams' => self::$buffer
            ]);

            if($response->status() !== 200)
            {
                throw new Exception('Ошибка отправки в loki, статус ответа ' . $response->status());
            }

            self::$buffer = []; // Очищаем буфер при успехе
        } catch (Exception $e) {
            LoggerHelper::getLogger('loki')->error($e->getMessage());
            //TODO написть отправку уведомлений если вдруг loki не доступен
            // Если отправка не удалась, сохраняем в файл
            self::$buffer = [];
        }
    }
}