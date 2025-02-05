<?php
namespace Common\Helpers;

use Illuminate\Support\Facades\Http;
use Exception;

class LokiLogger
{
    /**
     * @var array
     */
    private static array $buffer = [];
    /**
     * @var int
     */
    private static int $batchSize = 100;

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
     * @param $message
     * @param $app
     * @param string $level
     */
    public static function log($message, $app = 'laravel', string $level = 'info')
    {
        $formattedMessage = str_replace(PHP_EOL, "\n", $message);

        $timestamp = (int) floor(microtime(true) * 1e9);

// Превращаем в строку
        $timestampString = (string) $timestamp;

        self::$buffer[] = [
            "stream" => ["app" => $app, "level" => $level],
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