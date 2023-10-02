<?php

namespace Common\Helpers\Curls\Cbond;

use Carbon\Carbon;
use Common\Helpers\Curls\Curl;
use Common\Helpers\LoggerHelper;
use Cache;
use App;
use Exception;

class CbondCurl extends Curl
{
    /**
     *
     */
    public const API_URL = 'http://46.8.220.5:8000';

    /**
     *
     */
    protected const CURLOPT_CONNECTTIMEOUT = 30;
    /**
     *
     */
    protected const CURLOPT_TIMEOUT = 30;
    /**
     *
     */
    protected const COMMAND_CURLOPT_CONNECTTIMEOUT = 30;
    /**
     *
     */
    protected const COMMAND_CURLOPT_TIMEOUT = 30;

    /**
     * @return int
     */
    public static function getTimeout()
    {
        if (App::runningInConsole()) {
            return static::COMMAND_CURLOPT_TIMEOUT;
        }else{
            return static::CURLOPT_TIMEOUT;
        }
    }

    /**
     * @return int
     */
    public static function getConnectionTimeout()
    {
        if (App::runningInConsole()) {
            return static::COMMAND_CURLOPT_CONNECTTIMEOUT;
        }else{
            return static::CURLOPT_CONNECTTIMEOUT;
        }
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
            if (Cache::has('cbond' . $searchText)) {
                return Cache::get('cbond' . $searchText);
            }

            $url = self::API_URL;
            $coockies = '';

            $response = json_decode(self::get($url, [
                'text' => $searchText,
                'cache' => $cache,
            ], [], 'cbond', $coockies));

            if (isset($response->result) && $response->result === 'success')
            {
                Cache::add('cbond' . $searchText, 1, Carbon::now()->addDay());
            }
        } catch (Exception $e) {
            LoggerHelper::getLogger('cbond-stock')->error($e);
            return false;
        }
    }
}
