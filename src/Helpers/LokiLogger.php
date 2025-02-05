<?php
namespace Common\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class LokiLogger
{
    private static array $buffer = [];
    private static int $batchSize = 100;

    /**
     * @param string $message
     * @param string $level
     */
    public static function log(string $message, string $level = 'info')
    {
        self::$buffer[] = [
            'stream' => ['app' => 'laravel', 'level' => $level],
            'values' => [[(string) (microtime(true) * 1e9), $message]]
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
            Http::post('http://localhost:3100/loki/api/v1/push', [
                'streams' => self::$buffer
            ]);
            self::$buffer = []; // Очищаем буфер при успехе
        } catch (\Exception $e) {
            //TODO написть отправку уведомлений если вдруг loki не доступен
            // Если отправка не удалась, сохраняем в файл
            self::$buffer = [];
        }
    }
}
