<?php

namespace Common\Helpers\Curls\Cbond;

use Carbon\Carbon;
use Common\Helpers\Curls\Curl;
use Common\Helpers\LoggerHelper;
use Common\Helpers\Translit;
use Cache;
use Common\Models\Catalog\Cbond\CbondStock;

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

            [$original, $text, $translitText] = Translit::make($searchText);
            $url = self::API_URL . $searchText;
            $coockies = '';

            $response = json_decode(Curl::get($url, [], [], 'cbond', $coockies));

            if (isset($response['result']) && $response['result'] === 'success') {
                $stock = CbondStock::where(function ($query) use ($searchText) {
                    $query->where('isin', $searchText)
                        ->orWhere('shortname', $searchText)
                        ->orWhere('name', $searchText)
                        ->orWhere('secid', $searchText);
                })
                    ->first();

                if ($stock) {
                    Cache::add('cbond' . $searchText, $stock, Carbon::now()->addDay());
                    return $stock;
                }

                Cache::add('cbond' . $searchText, false, Carbon::now()->addDay());
                return false;
            }

            return false;
        } catch (Exception $e) {
            LoggerHelper::getLogger('cbond-stock')->error($e);
            return false;
        }
    }
}
