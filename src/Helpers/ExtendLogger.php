<?php

namespace Common\Helpers;

use App\Helpers\Curls\TelegramCurl;
use Exception;
use Monolog\Logger;

class ExtendLogger extends Logger
{
    public function error($message, array $context = []): void
    {
        parent::error($message, $context);

        if (config('app.env') === 'production') {
            if ($message instanceof Exception) {
                $arrayMess = [
                    'message' => $message->getMessage(),
                    'file' => $message->getFile(),
                    'line' => $message->getLine(),
                    'context' => $context,
                ];
            } else {
                $arrayMess = [
                    'message' => $message,
                    'context' => $context,
                ];
            }

            TelegramCurl::postMessage(TelegramCurl::captionForFile($arrayMess), TelegramCurl::FIN_ERROR_CHAT_ID);
        }
    }
}