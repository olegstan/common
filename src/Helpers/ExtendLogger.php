<?php

namespace Common\Helpers;

use Common\Helpers\Curls\TelegramCurl;
use Exception;
use Monolog\Logger;

class ExtendLogger extends Logger
{
    public function error($message, array $context = []): void
    {
        parent::error($message, $context);

        if (config('app.env') != 'production') {
            return;
        }

        if ($message instanceof Exception) {
            $this->sendTelegram([
                'message' => $message->getMessage(),
                'file' => $message->getFile(),
                'line' => $message->getLine(),
                'context' => $context,
            ]);
            return;
        }

        try {
            //Если ошибка не из эксепшена прилетела, скорее всего она из Monolog
            //Полную ошибку получить не удается, но хотя бы можно узнать путь до файла с ошибкой
            $text = $this->object_to_array($this->getHandlers()[0]);//Объект Monolog
            if (is_array($text)) {
                $keys = array_keys($text); //Массив всех ключей, где выбираем url
                $text = $text[$keys[8]]; //Получаемый путь
            }

            $this->sendTelegram(['path' => $text]);
        } catch (Exception $e) {
            $this->sendTelegram([
                'error' => 'Не удалось обработать ошибку для отправки',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param array $message
     *
     * @return void
     */
    public function sendTelegram(array $message)
    {
        TelegramCurl::postMessage(
            TelegramCurl::captionForFile($message) .
            "\n" .
            config('app.url'),
            TelegramCurl::FIN_ERROR_CHAT_ID,
        );
    }

    /**
     * @param $obj
     *
     * @return array
     */
    public function object_to_array($obj): array
    {
        if (is_object($obj) || is_array($obj)) {
            $ret = (array)$obj;
            foreach ($ret as &$item) {
                $item = $this->object_to_array($item);
            }
            return $ret;
        }

        return $obj;
    }
}