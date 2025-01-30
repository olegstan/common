<?php

namespace Common\Helpers\Curls;

use Common\Helpers\LoggerHelper;
use CURLFile;

class TelegramCurl extends Curl
{
    public const TELEGRAM_TOKEN = '5932194327:AAHM8u5VQU5b9JcaJIFyMVf9O9TKEyLRWOM';

    /**
     * fintest_monitor
     *
     * @var string
     */
    public const FINTEST_MONITOR_CHAT_ID = '-893924565';

    /**
     * fin_aton_error
     *
     * @var string
     */
    public const FIN_PARSE_ERROR_CHAT_ID = '@fin_parse_error';

    /**
     * Test chat by MasyaSm
     *
     * @var string
     */
    public const MASYA_TEST_CHAT_ID = '-4197342859';

    /**
     * Logger error chat
     *
     * @var string
     */
    public const FIN_ERROR_CHAT_ID = '-1002245845350';
    
    /**
     * Logger error chat
     *
     * @var string
     */
    public const FIN_VALID_ERROR_CHAT_ID = '-4624306641';

    /**
     * @param string $text
     * @param string|null $chatId
     *
     * @return mixed|void
     */
    public static function postMessage(string $text, ?string $chatId = null)
    {
        // URL API Telegram
        $url = "https://api.telegram.org/bot" . self::TELEGRAM_TOKEN . "/sendMessage";

        // Формируем данные для отправки
        $data = json_encode([
            'chat_id' => $chatId ?? self::FINTEST_MONITOR_CHAT_ID,
            'text' => $text,
            'disable_notification' => true, // Отключает уведомление (если нужно)
            'parse_mode' => 'HTML', // Поддержка HTML-разметки
        ]);

        $response = self::post($url, $data, [
            'Content-Type: application/json',
        ], 'telegram');

        // Обрабатываем ответ
        $json = json_decode($response);

        // Если запрос выполнен успешно
        if (isset($json->ok) && $json->ok) {
            return $json; // Возвращаем ответ API
        }

        // Если запрос не удался, логируем ошибку
        LoggerHelper::getLogger()->error('Ошибка отправки сообщения в Telegram', [
            'url' => $url,
            'data' => $data,
            'response' => $response,
        ]);

        return null;
    }

    /**
     * @param array $fileContents
     * @param array $caption
     *
     * @return mixed|void
     */
    public static function postFile(array $fileContents, array $caption)
    {
        $url = "https://api.telegram.org/bot" . TelegramCurl::TELEGRAM_TOKEN . "/sendDocument";

        $tempFilePath = self::createFile($fileContents);

        // Отправка файла и текста в Telegram
        $postFields = [
            'chat_id' => TelegramCurl::FINTEST_MONITOR_CHAT_ID,
            'document' => new CURLFile($tempFilePath),
        ];

        self::postMessage(self::captionForFile($caption));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        $response = curl_exec($ch);

        // Удаление временного файла
        unlink($tempFilePath);

        $json = json_decode($response);

        if (isset($json->ok) && $json->ok) {
            return $json;
        }
    }

    /**
     * @param $fileContents
     *
     * @return string
     */
    public static function createFile($fileContents): string
    {
        // Преобразование данных в JSON
        $jsonData = json_encode($fileContents);

        // Создание временного файла и запись данных в формате JSON
        $tempFilePath = tempnam(sys_get_temp_dir(), 'valuation_file');
        $tempFilePath = str_replace('.tmp', '', $tempFilePath);
        $tempFilePath .= '.json';
        file_put_contents($tempFilePath, $jsonData);
        return $tempFilePath;
    }

    /**
     * @param $caption
     *
     * @return array|string|string[]|null
     */
    public static function captionForFile($caption)
    {
        return preg_replace(
            "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/",
            "\n",
            (str_replace(['{', '}', ',', '[', ']', '"'],
                ['', '', '', '', '', ''],
                json_encode($caption, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))),
        );
    }
}
