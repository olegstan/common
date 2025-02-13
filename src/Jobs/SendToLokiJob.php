<?php

namespace Common\Jobs;

use Common\Helpers\LoggerHelper;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendToLokiJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Массив логов, которые нужно отправить.
     *
     * @var array
     */
    protected array $logs;

    /**
     * @param array $logs
     */
    public function __construct(array $logs)
    {
        $this->logs = $logs;
    }

    /**
     * Логика отправки логов в Loki через метод fire.
     *
     * @param mixed $job
     * @param array $data
     */
    public function fire($job, $data)
    {
        try {
            $user     = config('loki.user');
            $password = config('loki.password');

            $http = Http::withOptions([]);
            if ($user && $password) {
                $http = $http->withBasicAuth($user, $password);
            }

            // Отправляем данные в Loki
            $response = $http->post(config('loki.host') . '/loki/api/v1/push', [
                'streams' => $this->logs,
            ]);

            // Проверяем статус ответа
            if (!in_array($response->status(), [200, 204])) {
                throw new Exception('Ошибка отправки в Loki: статус ' . $response->status());
            }

            // Если всё ок, удаляем задачу
            $job->delete();

        } catch (Exception $e) {
            // Логируем ошибку
            LoggerHelper::getLogger()->error('SendToLokiJob failed: ' . $e->getMessage());

            // Попробуем повторить отправку позже
            if ($job->attempts() < 3) {
                $job->release(300); // Повтор через 5 минут
            } else {
                $job->delete();
            }
        }
    }
}

