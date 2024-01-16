<?php

namespace Common\Helpers\Curls\Finex;

use App;
use Cache;
use Carbon\Carbon;
use Common\Helpers\Curls\Curl;
use Common\Helpers\LoggerHelper;
use Exception;

class FinexCurl extends Curl
{
    /**
     *
     */
    public const API_URL = 'http://46.8.220.5:8001';

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
    public static $commandConnectTimeout = 3000;
    /**
     *
     */
    public static $commandTimeout = 3000;

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
            if (Cache::tags(['catalog'])->has('finex' . $searchText)) {
                return Cache::tags(['catalog'])->get('finex' . $searchText);
            }

            $url = self::API_URL;
            $coockies = '';

            $start = microtime(true);
            $response = json_decode(self::get($url, [
                'text' => $searchText,
                'cache' => $cache,
            ], [], 'finex', $coockies));

            $time = number_format((microtime(true) - $start), 2);

            if (isset($response->result) && $response->result === 'success')
            {
                Cache::tags(['catalog'])->add('finex' . $searchText, 1, Carbon::now()->addDay());
            }

            return $time;
        } catch (Exception $e) {
            LoggerHelper::getLogger('finex-stock')->error($e);
            return false;
        }
    }
}
