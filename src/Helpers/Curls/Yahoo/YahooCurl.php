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

            $responses = self::newSearchFunc($words);
//            $responses = self::oldSearchFunc($words);
//
//            $allData = [];
//            foreach ($responses as $response) {
//                $data = YahooDecoder::transformSearchResult($response);
//
//                if (is_array($data)) {
//                    $allData = array_merge($allData, $data);
//                }
//            }

            $newArr = [];
            foreach ($responses as $val) {
                if (isset($val['symbol'])) {
                    $newArr[$val['symbol']] = $val;
                }
            }

            $arr = array_values($newArr);
            Cache::tags([config('cache.tags')])->put('yahoo' . $searchText, $arr, Carbon::now()->addDay());

            return $arr;
        } catch (Exception $e) {
            LoggerHelper::getLogger('yahoo')->error($e);
            return false;
        }
    }

    /**
     * @param $words
     *
     * @return array
     */
    public static function oldSearchFunc($words): array
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

        return Curl::multiGet($urls);
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
        return ApiClientFactory::createApiClient()->search($words);
    }

    /**
     * @param $response
     *
     * @return bool|mixed
     */
    public static function extractCrumb($response)
    {
        if (preg_match('#CrumbStore":{"crumb":"(?<crumb>.+?)"}#', $response, $match)) {
            return json_decode('"' . $match['crumb'] . '"');
        }

        LoggerHelper::getLogger('yahoo')->error('Cannot extract crumb');
        return false;
    }

    /**
     * @param $symbol
     * @param $interval
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return array|false
     */
    public static function getHistoricalData($symbol, $interval, Carbon $startDate, Carbon $endDate)
    {
        try {
            $url = 'https://query1.finance.yahoo.com/v7/finance/download/' . urlencode($symbol);

            $response = Curl::get($url, [
                'period1' => $startDate->timestamp,
                'period2' => $endDate->timestamp,
                'interval' => $interval,
                'events' => 'history',
            ], [

            ], 'yahoo', self::$cookies);

            return YahooDecoder::transformHistoricalDataResult($response);
        } catch (Exception $e) {
            LoggerHelper::getLogger('yahoo')->error('Cannot extract crumb');

            return false;
        }
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
            LoggerHelper::getLogger('yahoo')->error('Cannot extract crumb');

            return false;
        }
    }
}
