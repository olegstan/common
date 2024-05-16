<?php

namespace Common\Jobs;

use App\Jobs\Base\Job;
use Common\Helpers\Curls\TradingView\TradingViewCurl;
use Common\Helpers\LoggerHelper;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeStock;
use Common\Models\Catalog\Yahoo\YahooStock;
use Exception;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Queue\SerializesModels;
use Throwable;

class TradingViewJob extends Job
{
    use SerializesModels;

    /**
     * @param RedisJob $job
     * @param $data
     * @throws Throwable
     */
    public function fire($job, $data)
    {
        [$type, $ids] = $data;

        try
        {
            if($type && $ids)
            {
                switch ($type)
                {
                    case 'moscow':
                        $stocks = MoscowExchangeStock::whereIntegerInRaw('id', $ids)
                            ->get();

                        foreach ($stocks as $stock)
                        {
                            TradingViewCurl::saveImageMoscowStock($stock);
                            TradingViewCurl::createTickers($stock->secid);
                        }
                        break;
                    case 'yahoo':
                        $stocks = YahooStock::whereIntegerInRaw('id', $ids)
                            ->get();

                        foreach ($stocks as $stock)
                        {
                            TradingViewCurl::saveImageYahooStock($stock);
                            TradingViewCurl::createTickers($stock->symbol);
                        }
                        break;
                }

            }

            $job->delete();
        }catch (Exception $e){
            LoggerHelper::getLogger()->error($e);
            $job->fail($e);
        }
    }
}
