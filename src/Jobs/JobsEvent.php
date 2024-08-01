<?php

namespace Common\Jobs;

use Cache;
use Carbon\Carbon;
use Common\Helpers\LoggerHelper;
use Common\Models\Users\Notification\UserNotification;
use Exception;

class JobsEvent
{
    public const PENDING = 0;
    public const STARTED = 1;
    public const PROCESSING = 2;
    public const FINISHED = 3;
    public const FAIL = 4;

    private ?int $time = null;
    private int $userId;
    private string $jobId;
    private int $jobType;
    private ?int $accountId = null;

    /**
     * Конструктор JobsEvent.
     *
     * @param int $userId
     * @param string $jobId
     * @param int $jobType
     */
    public function __construct(int $userId, string $jobId, int $jobType)
    {
        $this->userId = $userId;
        $this->jobId = $jobId;
        $this->jobType = $jobType;
    }

    /**
     * Статический метод для создания нового экземпляра JobsEvent.
     *
     * @param int $userId
     * @param string $jobId
     * @param int $jobType
     *
     * @return JobsEvent
     */
    public static function create(int $userId, string $jobId, int $jobType): JobsEvent
    {
        return new self($userId, $jobId, $jobType);
    }

    /**
     * Оповестите, что задание находится в очереди и инициализируйте кэш
     *
     * @return void
     */
    public function pending(): void
    {
        Cache::tags(config('cache.tags'))->forever('job_id.' . $this->jobId, 0);
        $this->updateJobStatus(self::PENDING);
    }

    /**
     * Запустите задание.
     *
     * @return void
     */
    public function start(): void
    {
        $this->updateJobStatus(self::STARTED);
    }

    /**
     * Завершите задание и при необходимости создайте уведомление.
     *
     * @param string|null $text
     *
     * @return void
     */
    public function finish(?string $text = null): void
    {
        Cache::tags(config('cache.tags'))->forget('job_id.' . $this->jobId);
        $this->updateJobStatus(self::FINISHED);

        if ($text) {
            $this->createNotification($text);
        }
    }

    /**
     * Отметьте задание как невыполненное.
     *
     * @return void
     */
    public function fail(): void
    {
        $this->updateJobStatus(self::FAIL);
    }

    /**
     * Обновите статус обработки задания.
     *
     * @param int $done
     * @param int $counts
     * @param ?int $accountId
     *
     * @return void
     */
    public function processing(int $done, int $counts, ?int $accountId = null): void
    {
        if($done > 0)
        {
            $round = round($counts * 100 / $done, 1);
            $this->accountId = $accountId;

            Cache::tags(config('cache.tags'))->forever('job_id.' . $this->jobId, $round);
            $this->updateJobStatus(self::PROCESSING, time());
        }
    }

    /**
     * Обновляйте статус задания и отправляйте события.
     *
     * @param int $status
     * @param int|null $time
     *
     * @return void
     */
    private function updateJobStatus(int $status, ?int $time = null): void
    {
        if (is_null($time) || $this->time + 5 < $time) {
            $percent = Cache::tags(config('cache.tags'))->get('job_id.' . $this->jobId);

            try {
                //для каталога это логика не нужна, там и класса этого нет
                if(class_exists(App\Events\JobsStatus::class))
                {
                    $data = [
                        'user_id' => $this->userId,
                        'job_type' => $this->jobType,
                        'percent' => $percent,
                        'status' => $status,
                        'job_id' => $this->jobId,
                        'account_id' => $this->accountId,
                    ];

                    event(new App\Events\JobsStatus($data, 'client'));
                    event(new App\Events\JobsStatus($data, 'manager'));
                }
            } catch (Exception $e) {
                LoggerHelper::getLogger('jobsevent')->error($e->getMessage());
            }

            $this->time = $time;
        }
    }

    /**
     * Создайте уведомление для пользователя.
     *
     * @param string $text
     *
     * @return void
     */
    private function createNotification(string $text): void
    {
        $message = "Синхронизация с $text успешно выполнена.";
        $notification = UserNotification::where('user_id', $this->userId)
            ->where('content', $message)
            ->where('status', UserNotification::CONFIRMED)
            ->where('action_id', null)
            ->first();

        if (!$notification) {
            UserNotification::create([
                'content' => $message,
                'user_id' => $this->userId,
                'status' => UserNotification::CONFIRMED,
                'action_id' => null,
            ]);
        } else {
            $notification->created_at = Carbon::now();
            $notification->save();
        }
    }
}