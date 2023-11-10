<?php

namespace Common\Jobs;

use Carbon\Carbon;
use Common\Helpers\Curls\TradingView\TradingViewCurl;
use Common\Helpers\LoggerHelper;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeStock;
use Exception;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Queue\SerializesModels;
use Throwable;

class MoscowExchangeDataJob extends Job
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
            $from = Carbon::now()->subDays(10);
            $till = Carbon::now();

            if($ids)
            {
                /**
                 * @var MoscowExchangeStock[] $stocks
                 */
                $stocks = MoscowExchangeStock::whereIntegerInRaw('id', $ids)
                    ->get();

                $tickers = [];
                foreach ($stocks as $stock)
                {
                    if($stock->groupname === 'Акции')
                    {
                        $tickers[] = TradingViewCurl::tickersExplode($stock->secid);
                    }

                    MoscowExchangeStock::loadHistory($stock, $from, $till);
                }

                TradingViewCurl::parseData('symbol', $tickers);
            }
            $job->delete();
        }catch (Exception $e){
            LoggerHelper::getLogger()->error($e);
            $job->fail($e);
        }
    }
}
