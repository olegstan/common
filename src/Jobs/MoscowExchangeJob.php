<?php

namespace Common\Jobs;

use Common\Helpers\LoggerHelper;
use Common\Jobs\Base\CreateJobs;
use Common\Jobs\Base\Job;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeStock;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;
use Exception;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Queue;
use Throwable;


class MoscowExchangeJob extends Job
{
    use SerializesModels;

    public const TYPE = DefinitionActiveConst::MOEX_PROFITABILITY;

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
                $stocks = MoscowExchangeStock::whereIntegerInRaw('id', $ids)
                    ->get();

                foreach ($stocks as $stock) {
                    MoscowExchangeStock::loadCoupons($stock);
                    MoscowExchangeStock::loadDividends($stock);
                }

                CreateJobs::create(TradingViewJob::class, ['moscow', $ids]);
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
