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
                'path_to_error' => LoggerHelper::$commandKey,
            ]);
            return;
        }

        try {
            //Если ошибка не из эксепшена прилетела, скорее всего она из Monolog
            //Полную ошибку получить не удается, но хотя бы можно узнать путь до файла с ошибкой
            if (is_array($this->getHandlers()[0])) {
                $text = $this->getHandlers()[0];//Объект Monolog
                $keys = array_keys($text); //Массив всех ключей, где выбираем url
                $text = $text[$keys[8]]; //Получаемый путь
            }elseif (is_object($this->getHandlers()[0])) {
                $text = $this->object_to_array($this->getHandlers()[0]);//Объект Monolog
                $keys = array_keys($text); //Массив всех ключей, где выбираем url
                $text = $text[$keys[8]]; //Получаемый путь
            } else{
                $text = $this->getHandlers()[0];
            }

            $this->sendTelegram([
                'path' => $text,
                'messeage' => $message,
                'context' => $context,
                'path_to_error' => LoggerHelper::$commandKey,
            ]);
        } catch (Exception $e) {
            $this->sendTelegram([
                'error' => 'Не удалось обработать ошибку для отправки',
                'exception' => $e->getMessage(),
                'path_to_error' => LoggerHelper::$commandKey,
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
     * @return mixed
     */
    public function object_to_array($obj)
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