<?php

namespace Common\Jobs\Exchanges;

use Common\Helpers\LoggerHelper;
use Common\Jobs\Base\CreateJobs;
use Common\Jobs\Base\Job;
use Common\Jobs\TradingViewJob;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;
use Exception;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Queue\SerializesModels;
use Throwable;

class YahooJob extends Job
{
    use SerializesModels;

    public const TYPE = DefinitionActiveConst::YAHOO_DATA;

    /**
     * @param RedisJob $job
     * @param $data
     *
     * @throws Throwable
     */
    public function fire($job, $data)
    {
        [$ids] = $data;

        try {
            if ($ids) {
                CreateJobs::create(TradingViewJob::class, ['yahoo', $ids]);
            }
            if ($job) {
                $job->delete();
            }
        } catch (Exception $e) {
            LoggerHelper::getLogger()->error($e);
            if ($job) {
                $job->fail($e);
            }
        }
    }
}
