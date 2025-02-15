<?php

namespace Common\Helpers\Curls\Cbond;

use App;
use Cache;
use Carbon\Carbon;
use Common\Helpers\Curls\Curl;
use Common\Helpers\LoggerHelper;
use Exception;

class CbondCurl extends Curl
{
    /**
     *
     */
    public const API_URL = 'http://37.46.132.67:8000';

    /**
     *
     */
    public static $connectTimeout = 10;
    /**
     *
     */
    public static $timeout = 10;
    /**
     *
     */
    public static $commandConnectTimeout = 30;
    /**
     *
     */
    public static $commandTimeout = 30;

    /**
     * @return int
     */
    public static function getTimeout()
    {
        if (App::runningInConsole()) {
            return static::$commandTimeout;
        }

        return static::$timeout;
    }

    /**
     * @return int
     */
    public static function getConnectionTimeout()
    {
        if (App::runningInConsole()) {
            return static::$commandConnectTimeout;
        }

        return static::$connectTimeout;
    }

    /**
     * @param $searchText
     * @param string $lang
     * @param int $limit
     * @param bool $cache
     * @return false|mixed
     */
    public static function search($searchText, string $lang = 'ru', int $limit = 50, bool $cache = true)
    {
        try {
            if (Cache::tags([config('cache.tags')])->has('cbond' . $searchText)) {
                return Cache::tags([config('cache.tags')])->get('cbond' . $searchText);
            }

            $url = self::API_URL;
            $coockies = '';

            $start = microtime(true);
            $response = json_decode(self::get($url, [
                'text' => $searchText,
                'cache' => $cache,
            ], [], 'cbond', $coockies));

            $time = number_format((microtime(true) - $start), 2);

            if (isset($response->result) && $response->result === 'success')
            {
                Cache::tags([config('cache.tags')])->add('cbond' . $searchText, 1, Carbon::now()->addDay());
            }

            return $time;
        } catch (Exception $e) {
            LoggerHelper::getLogger('cbond-stock')->error($e);
            return false;
        }
    }
}
