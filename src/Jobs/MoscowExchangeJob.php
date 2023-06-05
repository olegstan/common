<?php

namespace Common\Jobs;

use Common\Helpers\LoggerHelper;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeStock;
use Exception;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Queue;
use Throwable;


class MoscowExchangeJob extends Job
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
                $stocks = MoscowExchangeStock::whereIntegerInRaw('id', $ids)
                    ->get();

                foreach ($stocks as $stock)
                {
                    MoscowExchangeStock::loadCoupons($stock);
                    MoscowExchangeStock::loadDividends($stock);
                }

                Queue::push(TradingViewJob::class, ['moscow', $ids]);
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
