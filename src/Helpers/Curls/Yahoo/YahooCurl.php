<?php

namespace Common\Helpers\Curls\Yahoo;

use Cache;
use Carbon\Carbon;
use Common\Helpers\Curls\Curl;
use Common\Helpers\LoggerHelper;
use Common\Helpers\Translit;
use Exception;
use Scheb\YahooFinanceApi\ApiClientFactory;
use Scheb\YahooFinanceApi\Exception\ApiException;
use Scheb\YahooFinanceApi\Results\SearchResult;

/**
 * Class YahooCurl
 *
 * $res = YahooCurl::search('Apple');
 *
 * $res = YahooCurl::getHistoricalData("AAPL", YahooCurl::INTERVAL_1_DAY, Carbon::now()->subMonth(), Carbon::now());
 *
 * $res = YahooCurl::getQuotes(["AAPL"]);
 *
 * @package Common\Helpers\Curls\Yahoo
 */
class YahooCurl
{
    public const INTERVAL_1_DAY = '1d';
    public const INTERVAL_1_WEEK = '1wk';
    public const INTERVAL_1_MONTH = '1mo';

    /**
     * @var string
     */
    protected static string $cookies = '';

    /**
     * @param $text
     * @param bool $cache
     *
     * @return array|false
     */
    public static function search($text, bool $cache = true)
    {
        $searchText = $text;

        try {
            if ($cache && Cache::tags([config('cache.tags')])->has('yahoo' . $searchText)) {
                return Cache::tags([config('cache.tags')])->get('yahoo' . $searchText);
            }

            [$original, $text, $translitText] = Translit::make($text);

            $words = preg_split("/[\-\+\<\>\@\(\)\~*\ ']/", $text);

//            $arr = self::oldSearchFunc($words);
            $arr = self::newSearchFunc($text);

            Cache::tags([config('cache.tags')])->put('yahoo' . $searchText, $arr, Carbon::now()->addDay());

            return $arr;
        } catch (Exception $e) {
            LoggerHelper::getLogger('yahoo')->error($e);
            return false;
        }
    }

    /**
     * @param array $words
     *
     * @return array
     * @throws Exception
     */
    public static function oldSearchFunc(array $words): array
    {
        $urls = [];

        foreach ($words as $word) {
            $urls[] = [
                'url' => 'https://query2.finance.yahoo.com/v1/finance/search',
                'params' => [
                    'q' => $word,
                    'lang' => 'en-US',
                    'region' => 'US',
                    'quotesCount' => '6',
                    'newsCount' => '4',
                    'enableFuzzyQuery' => 'false',
                    'quotesQueryId' => 'tss_match_phrase_query',
                    'multiQuoteQueryId' => 'multi_quote_single_token_query',
                    'newsQueryId' => 'news_cie_vespa',
                    'enableCb' => 'true',
                    'enableNavLinks' => 'true',
                    'enableEnhancedTrivialQuery' => 'true',
                ],
                'headers' => [],
            ];
        }

        $responses = Curl::multiGet($urls);

        $allData = [];
        foreach ($responses as $response) {
            $data = YahooDecoder::transformSearchResult($response);

            if (is_array($data)) {
                $allData = array_merge($allData, $data);
            }
        }

        $arr = [];
        foreach ($allData as $val) {
            if (isset($val['symbol'])) {
                $arr[] = $val;
            }
        }

        return $arr;
    }

    /**
     * @param $words
     *
     * @return SearchResult[]
     * @throws ApiException
     */
    public static function newSearchFunc($words): array
    {
        // Returns an array of Scheb\YahooFinanceApi\Results\SearchResult
        $responses = ApiClientFactory::createApiClient()->search($words);

        $arr = [];
        foreach ($responses as $response) {
            $arr[] = [
                'symbol' => $response->getSymbol(),
                'exch' => $response->getExch(),
                'type' => $response->getType(),
                'exch_disp' => $response->getExchDisp(),
                'type_disp' => $response->getTypeDisp(),
            ];
        }

        return $arr;
    }

    /**
     * @param $response
     *
     * @return bool|mixed
     */
    public static function extractCrumb($response)
    {
        try {
            if (preg_match('#CrumbStore":{"crumb":"(?<crumb>.+?)"}#', $response, $match)) {
                return json_decode('"' . $match['crumb'] . '"');
            }
        } catch (Exception $e) {
            LoggerHelper::getLogger('yahoo')->error('Cannot extract crumb (extractCrumb)', [$e->getTraceAsString()]);
        }
        return false;
    }

    /**
     * @param $symbol
     * @param $interval
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return array
     */
    public static function getHistoricalData($symbol, $interval, Carbon $startDate, Carbon $endDate): array
    {
        try {
//            return self::oldHistoryData($symbol, $interval, $startDate, $endDate);
            return self::newHistoryData($symbol, $interval, $startDate, $endDate);
        } catch (Exception $e) {
//            LoggerHelper::getLogger('yahoo')->error(
//                'Не могу извлечь крошку',
//                ['symbol' => $symbol, 'interval' => $interval, 'startDate' => $startDate, 'endDate' => $endDate],
//            );

            return [];
        }
    }

    /**
     * @param $symbol
     * @param $interval
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return array
     * @throws Exception
     */
    public static function oldHistoryData($symbol, $interval, Carbon $startDate, Carbon $endDate): array
    {
        $url = 'https://query1.finance.yahoo.com/v7/finance/download/' . urlencode($symbol);

        $response = Curl::get($url, [
            'period1' => $startDate->timestamp,
            'period2' => $endDate->timestamp,
            'interval' => $interval,
            'events' => 'history',
        ], [

        ], 'yahoo', self::$cookies);

        return YahooDecoder::transformHistoricalDataResult($response);
    }

    /**
     * @param $symbol
     * @param $interval
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return array
     * @throws ApiException
     */
    public static function newHistoryData($symbol, $interval, Carbon $startDate, Carbon $endDate): array
    {
        $client = ApiClientFactory::createApiClient();
        $historicalData = $client->getHistoricalQuoteData(
            $symbol,
            $interval,
            $startDate,
            $endDate,
        );

        $arr = [];
        foreach ($historicalData as $historicalDatum) {
            $arr[] = [
                'date' => $historicalDatum->getDate(),
                'open' => $historicalDatum->getOpen(),
                'high' => $historicalDatum->getHigh(),
                'low' => $historicalDatum->getLow(),
                'close' => $historicalDatum->getClose(),
                'adj_close' => $historicalDatum->getAdjClose(),
                'volume' => $historicalDatum->getVolume(),
            ];
        }

        return $arr;
    }

    /**
     * @param $symbols
     *
     * @return array|bool
     */
    public static function getQuotes($symbols)
    {
        try {
            $url = 'https://query1.finance.yahoo.com/v7/finance/quote';
            $response = Curl::get($url, [
                'symbols' => urlencode(implode(',', $symbols)),
            ], [

            ], 'yahoo');

            return YahooDecoder::transformQuotes($response);
        } catch (Exception $e) {
            LoggerHelper::getLogger('yahoo')->error('Cannot extract crumb (getQuotes)', [$e->getTraceAsString()]);

            return false;
        }
    }

    /**
     * @param string $symbol
     * @param string $lang
     *
     * @return array
     */
    public static function getDividends(string $symbol, string $lang = 'ru'): array
    {
        $arr = [];

        try {
            $client = ApiClientFactory::createApiClient();
            $dividendData = $client->getHistoricalDividendData(
                $symbol,
                Carbon::now()->subYears(50),
                Carbon::now(),
            );

            if (empty($dividendData)) {
                return $arr;
            }

            foreach ($dividendData as $dividendDatum) {
                $arr[] = [
                    'date' => $dividendDatum->getDate()->format('Y-m-d'),
                    'value' => $dividendDatum->getDividends(),
                ];
            }

            return $arr;
        } catch (Exception $e) {
            //Если упало в ошибку, скорее всего просто нет данных
//            LoggerHelper::getLogger('yahoo-getDividends')->error($e, ['symbol' => $symbol]);
            return $arr;
        }
    }

    /**
     * @param string $symbol
     *
     * @return array
     */
    public static function getSplits(string $symbol): array
    {
        $arr = [];

        try {
            $client = ApiClientFactory::createApiClient();

            $splitData = $client->getHistoricalSplitData(
                $symbol,
                Carbon::now()->subYears(50),
                Carbon::now(),
            );

            if (empty($splitData)) {
                return $arr;
            }

            foreach ($splitData as $splitDatum) {
                $explode = explode(':', $splitDatum->getStockSplits());

                $arr[] = [
                    'date' => $splitDatum->getDate()->format('Y-m-d'),
                    'before' => $explode[0],
                    'after' => $explode[1],
                ];
            }

            return $arr;
        } catch (Exception $e) {
            //Если упало в ошибку, скорее всего просто нет данных
//            LoggerHelper::getLogger('yahoo-getSplits')->error($e, ['symbol' => $symbol]);
            return $arr;
        }
    }
}
