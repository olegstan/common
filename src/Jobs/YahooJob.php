<?php

namespace Common\Jobs;

use App\Jobs\Base\Job;
use Common\Helpers\LoggerHelper;
use Exception;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Queue;
use Throwable;

class YahooJob extends Job
{
    use SerializesModels;

    /**
     * @param RedisJob $job
     * @param $data
     * @throws Throwable
     */
    public function fire($job, $data)
    {
        [$ids] = $data;

        try{
            if($ids)
            {
                Queue::push(TradingViewJob::class, ['yahoo', $ids]);
            }
            if($job)
            {
                $job->delete();
            }
        }catch (Exception $e){
            LoggerHelper::getLogger()->error($e);
            if($job)
            {
                $job->fail($e);
            }
        }
    }
}
