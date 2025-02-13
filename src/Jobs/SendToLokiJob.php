<?php

namespace Common\Jobs;

use Common\Helpers\LoggerHelper;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendToLokiJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        try {
            if($data['options'])
            {
                unset($data['options']);
            }

            $user     = config('loki.user');
            $password = config('loki.password');

            $http = Http::withOptions([]);
            if ($user && $password) {
                $http = $http->withBasicAuth($user, $password);
            }

            // Отправляем данные в Loki
            $response = $http->post(config('loki.host') . '/loki/api/v1/push', [
                'streams' => $data,
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

