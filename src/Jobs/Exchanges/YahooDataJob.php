<?php

namespace Common\Jobs\Exchanges;

use Carbon\Carbon;
use Common\Helpers\Curls\TradingView\TradingViewCurl;
use Common\Helpers\LoggerHelper;
use Common\Jobs\Base\Job;
use Common\Models\Catalog\Yahoo\YahooStock;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;
use Exception;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Queue\SerializesModels;
use Throwable;

class YahooDataJob extends Job
{
    use SerializesModels;

    public const TYPE = DefinitionActiveConst::YAHOO_HISTORY;

    /**
     * @param RedisJob $job
     * @param $data
     *
     * @throws Throwable
     */
    public function fire($job, $data)
    {
        try {
            $from = Carbon::now()->subDays(10);
            $till = Carbon::now();

            if (!empty($data)) {
                /**
                 * @var YahooStock[] $stocks
                 */
                $stocks = YahooStock::whereIn('id', $data)
                    ->get();


                $tickers = [];
                foreach ($stocks as $stock) {
                    if ($stock->type_disp === 'Equity') {
                        $tickers[] = TradingViewCurl::tickersExplode($stock->symbol);
                    }

                    YahooStock::loadHistory($stock, $from, $till);
                }

                TradingViewCurl::parseData('symbol', $tickers);
            }
            $job->delete();
        } catch (Exception $e) {
            LoggerHelper::getLogger()->error($e);
            $job->fail($e);
        }
    }
}
