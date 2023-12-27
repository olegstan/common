<?php

namespace Common\Helpers\Curls\MoscowExchange;

use Cache;
use Carbon\Carbon;
use Common\Helpers\Curls\Curl;
use Common\Helpers\LoggerHelper;
use Common\Helpers\Translit;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeStock;
use Exception;

/**
 * Class MoscowExchangeCurl
 * @package Common\Helpers\Curls\Yahoo
 *
 * список всех бумаг https://iss.moex.com/iss/securities
 * история https://iss.moex.com/iss/history/engines/futures/markets/forts/boards/rfud/securities/NGF2
 * свечи https://iss.moex.com/iss/engines/futures/markets/forts/securities/NGF2/candles.json
 * Дата начала и окончания бумаги https://iss.moex.com/iss/history/engines/futures/markets/forts/securities/NGF2/dates
 * справочник значений https://iss.moex.com/iss/index.xml
 */
class MoscowExchangeCurl
{
    public const API_URL = 'http://iss.moex.com/iss/';

    public const AUTH_URL = 'https://passport.moex.com/authenticate';

    public const TIMEZONE = 'Europe/Moscow';

    public const DATE_FORMAT = 'Y-m-d';

    /**
     * @var string
     */
    protected static string $cookies = '';

    /**
     * @param $object
     * @return array|bool|float|int|string
     */
    public static function toArray($object)
    {
        $toArray = static function ($x) use (&$toArray) {
            if ($x) {
                return is_scalar($x)
                    ? $x
                    : array_map($toArray, (array)$x);
            }

            return '';
        };

        return $toArray($object);
    }

    /**
     * @param $cookies
     * @return bool
     */
    public static function auth(&$cookies)
    {
        $channel = 'moscow-exchange';

        try {
            $login = config('moscow-exchange.login');
            $password = config('moscow-exchange.password');


            $params = [];
            $url = self::AUTH_URL;

            $headers = [];
            if (!empty($cookies)) {
                $headers[] = 'Cookie: ' . $cookies;
            }

            $curl = curl_init($url);
            curl_setopt_array(
                $curl,
                $params + [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_COOKIESESSION => true,
                    CURLOPT_HEADER => true,
                    CURLOPT_TIMEOUT => 15,
                    CURLOPT_CONNECTTIMEOUT => 2,
                    CURLOPT_HTTPHEADER => $headers,
                    CURLINFO_HEADER_OUT => true,
                    CURLOPT_USERPWD => $login . ':' . $password,
                ]
            );

            $response = curl_exec($curl);
            LoggerHelper::getLogger($channel)->info("Исходящие заголовки: " . var_export(curl_getinfo($curl), true));
            LoggerHelper::getLogger($channel)->info("Параметры: " . var_export($params, true));

            $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $headers = array_filter(explode("\r\n", substr($response, 0, $header_size)));

            $response_headers = [];
            $certFound = false;
            foreach ($headers as $header) {
                if (stripos($header, ":")) {
                    $key = strtolower(mb_substr($header, 0, stripos($header, ":")));
                    $value = mb_substr($header, stripos($header, ":") + 1, mb_strlen($header) - stripos($header, ":"));
                    $response_headers[$key] = $value;

                    if ($key === 'set-cookie' && strpos($value, 'MicexPassportCert') !== false) {
                        $certFound = true;
                        $cookies = $value;
                    }
                } else {
                    continue;
                }
            }

            curl_close($curl);
            if ($certFound) {
                return true;
            }

            $response = substr($response, $header_size);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            LoggerHelper::getLogger($channel)->error($url);
            LoggerHelper::getLogger($channel)->error(var_export($response, true));
            LoggerHelper::getLogger($channel)->error(var_export($httpcode, true));

            return false;
        } catch (Exception $e) {
            LoggerHelper::getLogger($channel)->error($url);
            LoggerHelper::getLogger($channel)->error($e);
            return false;
        }
    }

    /**
     * @param $text
     * @param string $lang
     * @param int $limit
     * @param bool $cache
     * @return array|false|mixed
     */
    public static function search($text, string $lang = 'ru', int $limit = 50, bool $cache = true)
    {
        $searchText = $text;

        try {
            if ($cache && Cache::tag('catalog')->has('moex' . $searchText)) {
                return Cache::tag('catalog')->get('moex' . $searchText);
            }

            [$original, $text, $translitText] = Translit::make($text);

            //https://iss.moex.com/iss/securities/SBERP.json
            $words = preg_split("/[\-\+\<\>\@\(\)\~*\ ']/", $text);
            $wordTranslates = preg_split("/[\-\+\<\>\@\(\)\~*\ ']/", $translitText);

            $stocks = [];
            $urls = [

            ];

            foreach ($words as $word) {
                $urls[] = [
                    'url' => self::API_URL . 'securities.json',
                    'params' => ['q' => $word, 'limit' => $limit, 'lang' => $lang, 'iss.meta' => 'off'],
                    'headers' => [],
                ];
            }

            foreach ($wordTranslates as $wordTranslate) {
                $urls[] = [
                    'url' => self::API_URL . 'securities.json',
                    'params' => ['q' => $wordTranslate, 'limit' => $limit, 'lang' => $lang, 'iss.meta' => 'off'],
                    'headers' => [],
                ];
            }


            $responses = Curl::multiGet($urls);

            foreach ($responses as $response) {
                $arrayResponse = self::toArray(json_decode($response));

                $data = [];

                if (isset($arrayResponse['securities']['columns'], $arrayResponse['securities']['data'])
                    && is_array($arrayResponse['securities']['data'])) {
                    foreach ($arrayResponse['securities']['data'] as $datum) {
                        $data[] = array_combine($arrayResponse['securities']['columns'], $datum);
                    }

                    $stocks = array_merge($stocks, $data);
                }
            }

            if (!empty($stocks)) {
                $arr = array_unique($stocks, SORT_REGULAR);
                Cache::tags(['catalog'])->put('moex' . $searchText, $arr, Carbon::now()->addDay());

                return $arr;
            }

            return false;
        } catch (Exception $e) {
            LoggerHelper::getLogger('moscow-exchange')->error($e);
            return false;
        }
    }

    /**
     * @param $start
     * @param $limit
     * @param string $lang
     * @return false|string
     */
    public static function getList($start, $limit, $lang = 'ru')
    {
        try {
            //example https://iss.moex.com/iss/securities.json
            $response = Curl::get(
                self::API_URL . 'securities.json',
                [
                    'start' => $start,
                    'limit' => $limit,
                    'lang' => $lang,
                    'iss.meta' => 'off',
                ],
                [],
                'moscow-exchange',
                self::$cookies,
                false
            );

            $arrayResponse = self::toArray(json_decode($response));

            $data = [];
            if (isset($arrayResponse['securities']['columns'], $arrayResponse['securities']['data']) && is_array(
                    $arrayResponse['securities']['data']
                )) {
                foreach ($arrayResponse['securities']['data'] as $datum) {
                    $variant = array_change_key_case(
                        array_combine($arrayResponse['securities']['columns'], $datum),
                        CASE_LOWER
                    );

                    $data[] = $variant;
                }

                return $data;
            }
        } catch (Exception $e) {
            LoggerHelper::getLogger('moscow-exchange')->error($e);
            return false;
        }
    }

    /**
     * @param MoscowExchangeStock $stock
     * @param $from
     * @param $till
     * @param string $lang
     * @return array|bool
     */
    public static function getHistory(MoscowExchangeStock $stock, $from, $till, $lang = 'ru')
    {
        try {
            //example https://iss.moex.com/iss/history/engines/stock/markets/bonds/boards/TQBR/securities/RU000A105A95.xml?date=2018-12-19
            //example https://iss.moex.com/iss/history/engines/stock/markets/bonds/boards/TQBR/securities/XS2346922755.xml?date=2018-12-19
            //example https://iss.moex.com/iss/history/engines/stock/markets/shares/boards/TQBR/securities/SBERP.xml?date=2018-12-19
            $response = Curl::get(
                self::API_URL . 'history/engines/' . $stock->engine . '/markets/' . $stock->market . '/boards/' . $stock->primary_boardid . '/securities/' . $stock->secid . '.json',
                [
                    'from' => $from,
                    'till' => $till,
                    'lang' => $lang,
                    'iss.meta' => 'off',
                ],
                [],
                'moscow-exchange',
                self::$cookies,
                false
            );

            $arrayResponse = self::toArray(json_decode($response));

            $data = [];
            if (isset($arrayResponse['history']['columns'], $arrayResponse['history']['data']) && is_array(
                    $arrayResponse['history']['data']
                )) {
                foreach ($arrayResponse['history']['data'] as $datum) {
                    $variant = array_change_key_case(
                        array_combine($arrayResponse['history']['columns'], $datum),
                        CASE_LOWER
                    );

                    if ($variant['boardid'] === $stock->primary_boardid) {
                        $data[] = $variant;
                    }
                }

                return $data;
            }

            return false;
        } catch (Exception $e) {
            LoggerHelper::getLogger('moscow-exchange')->error($e);
            return false;
        }
    }

    /**
     * @param $code
     * @param string $lang
     * @param int $limit
     * @return array|bool
     */
    public static function getDescription($code, $lang = 'ru', $limit = 1)
    {
        try {
            //https://iss.moex.com/iss/securities/RU000A100ZP9.json?limit=1
            //https://iss.moex.com/iss/securities/RU000A105WJ8.json?limit=1
            //https://iss.moex.com/iss/securities/RU000A0ZZCD8.json?limit=1

            $response = Curl::get(self::API_URL . 'securities/' . $code . '.json', [
                'lang' => $lang,
                'limit' => $limit,
                'iss.meta' => 'off',
                'iss.only' => 'description'
            ], [], 'moscow-exchange', self::$cookies, false);

            $arrayResponse = self::toArray(json_decode($response));

            if (isset($arrayResponse['description']['columns'], $arrayResponse['description']['data']) && is_array(
                    $arrayResponse['description']['data']
                )) {
                $data = [];
                foreach ($arrayResponse['description']['data'] as $key => $datum) {
                    if (isset($datum[0], $datum[2])) {
                        $data[strtolower($datum[0])] = $datum[2];
                    }
                }

                return $data;
            }

            return false;
        } catch (Exception $e) {
            LoggerHelper::getLogger('moscow-exchange')->error($e);
            return false;
        }
    }

    /**
     * @param $code
     * @param string $lang
     * @return array|bool|null
     */
    public static function getCurrency($code, $lang = 'ru')
    {
        try {
            //example https://iss.moex.com/iss/securities/FXGD.json?limit=1

            $response = Curl::get(self::API_URL . 'securities/' . $code . '.json', [
                'lang' => $lang,
                'iss.meta' => 'off',
                'iss.only' => 'boards'
            ], [], 'moscow-exchange', self::$cookies, false);

            $arrayResponse = self::toArray(json_decode($response));

            $currency = [];

            if (isset($arrayResponse['boards']['columns'], $arrayResponse['boards']['data']) && is_array(
                    $arrayResponse['boards']['data']
                )) {
                foreach ($arrayResponse['boards']['data'] as $datum) {
                    if ($datum[15]) {
                        $currency[] = $datum[15];
                    }
                }
                $currency = array_values(array_unique($currency));
            }

            if (!empty($currency)) {
                return $currency;
            }

            return false;
        } catch (Exception $e) {
            LoggerHelper::getLogger('moscow-exchange')->error($e);
            return false;
        }
    }

    /**
     * @param $code
     * @param $lang
     * @param $limit
     * @return false
     */
    public static function getBoards($code, $lang = 'ru', $limit = 1)
    {
        try {
            //example https://iss.moex.com/iss/securities/FXGD.json?limit=1

            $response = Curl::get(self::API_URL . 'securities/' . $code . '.json', [
                'lang' => $lang,
                'limit' => $limit,
                'iss.meta' => 'off',
                'iss.only' => 'boards'
            ], [], 'moscow-exchange', self::$cookies, false);

            $arrayResponse = self::toArray(json_decode($response));

            $item = null;
            if (isset($arrayResponse['boards']['columns'], $arrayResponse['boards']['data']) && is_array(
                    $arrayResponse['boards']['data']
                )) {
                foreach ($arrayResponse['boards']['data'] as $key => $datum) {
                    $variant = array_change_key_case(
                        array_combine($arrayResponse['boards']['columns'], $datum),
                        CASE_LOWER
                    );

                    if (isset($variant['is_primary']) && $variant['is_primary'] === 1) {
                        $item = $variant;
                        break;
                    }
                }

                if ($item) {
                    return $item;
                }
            }

            return false;
        } catch (Exception $e) {
            LoggerHelper::getLogger('moscow-exchange')->error($e);
            return false;
        }
    }

    /**
     * @param MoscowExchangeStock $stock
     * @param $lang
     * @param $limit
     * @return false
     */
    public static function getData(MoscowExchangeStock $stock, $lang = 'ru', $limit = 1)
    {
        try {
            //example https://iss.moex.com/iss/engines/stock/markets/shares/boards/tqbr/securities/SBER.json

            $response = Curl::get(
                self::API_URL . 'engines/' . $stock->engine . '/markets/' . $stock->market . '/boards/' . $stock->primary_boardid . '/securities/' . $stock->secid . '.json',
                [
                    'lang' => $lang,
                    'limit' => $limit,
                    'iss.meta' => 'off',
                    'iss.only' => 'securities'
                ],
                [],
                'moscow-exchange',
                self::$cookies,
                false
            );

            $arrayResponse = self::toArray(json_decode($response));

            if (isset($arrayResponse['securities']['columns'], $arrayResponse['securities']['data']) && is_array(
                    $arrayResponse['securities']['data']
                )) {
                foreach ($arrayResponse['securities']['data'] as $datum) {
                    return array_change_key_case(
                        array_combine($arrayResponse['securities']['columns'], $datum),
                        CASE_LOWER
                    );
                }
            }

            return false;
        } catch (Exception $e) {
            LoggerHelper::getLogger('moscow-exchange')->error($e);
            return false;
        }
    }

    /**
     * @param $code
     * @param string $lang
     * @return array|bool
     */
    public static function getCoupons($code, $lang = 'ru')
    {
        try {
            //example https://iss.moex.com/iss/securities/RU000A102G01/bondization.xml
            $response = Curl::get(self::API_URL . 'securities/' . $code . '/bondization.json', [
                'lang' => $lang,
                'iss.meta' => 'off',
                'iss.only' => 'coupons'
            ], [], 'moscow-exchange', self::$cookies, false);

            $arrayResponse = self::toArray(json_decode($response));

            $items = [];
            if (isset($arrayResponse['coupons']['columns'], $arrayResponse['coupons']['data']) && is_array(
                    $arrayResponse['coupons']['data']
                )) {
                foreach ($arrayResponse['coupons']['data'] as $datum) {
                    $items[] = array_change_key_case(
                        array_combine($arrayResponse['coupons']['columns'], $datum),
                        CASE_LOWER
                    );
                }

                return $items;
            }

            return false;
        } catch (Exception $e) {
            LoggerHelper::getLogger('moscow-exchange')->error($e);
            return false;
        }
    }

    /**
     * @param $code
     * @param string $lang
     * @return array|bool
     */
    public static function getDividends($code, $lang = 'ru')
    {
        try {
            //example https://iss.moex.com/iss/securities/SBER/dividends.xml
            $response = Curl::get(self::API_URL . 'securities/' . $code . '/dividends.json', [
                'lang' => $lang,
                'iss.meta' => 'off',
                'iss.only' => 'dividends'
            ], [], 'moscow-exchange', self::$cookies, false);

            $arrayResponse = self::toArray(json_decode($response));

            $items = [];
            if (isset($arrayResponse['dividends']['columns'], $arrayResponse['dividends']['data']) && is_array(
                    $arrayResponse['dividends']['data']
                )) {
                foreach ($arrayResponse['dividends']['data'] as $datum) {
                    $items[] = array_change_key_case(
                        array_combine($arrayResponse['dividends']['columns'], $datum),
                        CASE_LOWER
                    );
                }

                return $items;
            }

            return false;
        } catch (Exception $e) {
            LoggerHelper::getLogger('moscow-exchange')->error($e);
            return false;
        }
    }

    /**
     * @param MoscowExchangeStock $stock
     * @param string $lang
     * @return bool
     */
    public static function getLastPrice(MoscowExchangeStock $stock, string $lang = 'ru')
    {
        try {
            //example https://iss.moex.com/iss/engines/stock/markets/shares/securities/AFLT.json
            $response = Curl::get(
                self::API_URL . 'engines/stock/markets/shares/securities/' . $stock->secid . '.json',
                [
                    'lang' => $lang,
                    'iss.meta' => 'off',
                    'iss.only' => 'marketdata'
                ],
                [],
                'moscow-exchange',
                self::$cookies,
                false
            );

            $arrayResponse = self::toArray(json_decode($response));

            $items = [];
            if (isset($arrayResponse['marketdata']['columns'], $arrayResponse['marketdata']['data']) &&
                is_array($arrayResponse['marketdata']['data'])) {
                foreach ($arrayResponse['marketdata']['data'] as $datum) {
                    $items[] = array_change_key_case(
                        array_combine($arrayResponse['marketdata']['columns'], $datum),
                        CASE_LOWER
                    );
                }

                if ($items) {
                    foreach ($items as $item) {
                        if (isset($item['boardid']) && $item['boardid'] === $stock->primary_boardid) {
                            return $item['last'];
                        }
                    }

                    return $items[0]['last'];
                }

                return false;
            }

            return false;
        } catch (Exception $e) {
            LoggerHelper::getLogger('moscow-exchange')->error($e);
            return false;
        }
    }

    /**
     * @param $secid
     * @param $market
     * @param string $lang
     * @return array|bool
     */
    public static function getFutures($secid, $market, string $lang = 'ru')
    {
        try {
            //example https://iss.moex.com/iss/engines/futures/markets/forts/securities/GZM2.json
            $response = Curl::get(
                self::API_URL . 'engines/futures/markets/' . $market . '/securities/' . $secid . '.json', [
                'lang' => $lang,
                'iss.meta' => 'off',
                'iss.only' => 'securities'
            ],
                [],
                'moscow-exchange',
                self::$cookies,
                false
            );

            $arrayResponse = self::toArray(json_decode($response));
            $items = [];
            if (isset($arrayResponse['securities']['columns'], $arrayResponse['securities']['data']) &&
                is_array($arrayResponse['securities']['data'])) {
                foreach ($arrayResponse['securities']['data'] as $datum) {
                    $items[] = array_change_key_case(
                        array_combine($arrayResponse['securities']['columns'], $datum),
                        CASE_LOWER
                    );
                }

                return $items;
            }

            return false;
        } catch (Exception $e) {
            LoggerHelper::getLogger('moscow-exchange')->error($e);
            return false;
        }
    }
}
