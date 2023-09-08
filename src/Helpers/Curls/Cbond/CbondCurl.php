<?php

namespace Common\Helpers\Curls\Cbond;

use Carbon\Carbon;
use Common\Helpers\Curls\Curl;
use Common\Helpers\LoggerHelper;
use Cache;

class CbondCurl
{
    public const API_URL = 'http://46.8.220.5:8000/?text=';

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
            if ($cache && Cache::has('cbond' . $searchText)) {
                return Cache::get('cbond' . $searchText);
            }

            $url = self::API_URL . $searchText;
            $coockies = '';

            $response = json_decode(Curl::get($url, [], [], 'cbond', $coockies));

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
