<?php

namespace Common\Helpers\Curls;

use App;
use Common\Helpers\LoggerHelper;

class Curl
{
    /**
     * Таймаут соединения для поиска с сайта
     */
    public static $connectTimeout = 30;

    /**
     * Таймаут соединения
     */
    public static $timeout = 30;

    /**
     * Таймаут соединения для консольных команд
     */
    public static $commandConnectTimeout = 300;

    /**
     * Таймаут для консольных команд
     */
    public static $commandTimeout = 300;

    /**
     * @var array
     */
    public static array $searchTimes = [];

    /**
     * @return int
     */
    public static function getTimeout(): int
    {
        return static::$timeout;
    }

    /**
     * @return int
     */
    public static function getConnectionTimeout(): int
    {
        return static::$connectTimeout;
    }

    /**
     * @param $requests
     * @param int $timeout
     * @param int $max_retries
     * @return array
     */
    public static function multiGet($requests, int $timeout = 10, int $max_retries = 3): array
    {
        $multi_handle = curl_multi_init();
        $handles = [];
        $urls = [];

        foreach ($requests as $key => $request) {
            $handles[$key] = self::initCurlHandle($request, $timeout);
            $urls[] = $request['url'] . ($request['params'] ? '?' . http_build_query($request['params']) : '');
            curl_multi_add_handle($multi_handle, $handles[$key]);
        }

        $responses = [];
        $completed = 0;
        $retry_count = array_fill_keys(array_keys($handles), 0);

        do {
            while (($status = curl_multi_exec($multi_handle, $running)) == CURLM_CALL_MULTI_PERFORM) {
                if ($status != CURLM_OK) {
                    break;
                }
            }

            while ($completed_handle = curl_multi_info_read($multi_handle)) {
                $completed_key = array_search($completed_handle['handle'], $handles, true);
                $completed_response = curl_multi_getcontent($completed_handle['handle']);
                $completed_code = curl_getinfo($completed_handle['handle'], CURLINFO_HTTP_CODE);
                $error = curl_error($completed_handle['handle']);

                $responses[$completed_key] = $completed_response;

                if (($completed_code != 200 || !empty($error)) && $retry_count[$completed_key] < $max_retries) {
                    self::logCurlError($request[$completed_key]['url'], $error, $completed_code);
                    $handles[$completed_key] = self::retryCurlHandle($multi_handle, $completed_handle['handle'], $requests[$completed_key], $timeout);
                    $retry_count[$completed_key]++;
                } else {
                    self::$searchTimes[$urls[array_key_first($urls)]] = curl_getinfo($handles[$completed_key], CURLINFO_TOTAL_TIME);
                    unset($urls[array_key_first($urls)]);
                    curl_multi_remove_handle($multi_handle, $completed_handle['handle']);
                    curl_close($completed_handle['handle']);
                    $completed++;
                }
            }
        } while ($running || $completed < count($requests));

        curl_multi_close($multi_handle);

        return $responses;
    }

    /**
     * @param $url
     * @param array $params
     * @param array $headers
     * @param $channel
     * @param string $cookies
     * @param bool $log
     * @param null $proxy
     * @return string
     */
    public static function get($url, $params = [], $headers = [], $channel, string &$cookies = '', bool $log = true, $proxy = null)
    {
        $url .= ($params ? '?' . http_build_query($params) : '');
        $curl = self::initCurlHandle(compact('url', 'headers', 'cookies'), static::getTimeout(), $proxy);
        $response = curl_exec($curl);

        if ($log) {
            self::logCurlResponse($curl, $channel, $url, $params, $response);
        }

        return self::processResponse($curl, $response, $cookies);
    }

    /**
     * @param $url
     * @param $params
     * @param array $headers
     * @param $channel
     * @param string $cookies
     * @param bool $log
     * @param null $proxy
     * @return string
     */
    public static function post($url, $params, $headers = [], $channel, string &$cookies = '', bool $log = true, $proxy = null)
    {
        $curl = self::initCurlHandle(compact('url', 'headers', 'cookies'), static::getTimeout(), $proxy);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        $response = curl_exec($curl);

        if ($log) {
            self::logCurlResponse($curl, $channel, $url, $params, $response);
        }

        return self::processResponse($curl, $response, $cookies);
    }

    /**
     * @param $url
     * @param $params
     * @param array $headers
     * @param $channel
     * @param string $cookies
     * @param bool $log
     * @param null $proxy
     * @return string
     */
    public static function put($url, $params, $headers = [], $channel, string &$cookies = '', bool $log = true, $proxy = null)
    {
        $curl = self::initCurlHandle(compact('url', 'headers', 'cookies'), static::getTimeout(), $proxy);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        $response = curl_exec($curl);

        if ($log) {
            self::logCurlResponse($curl, $channel, $url, $params, $response);
        }

        return self::processResponse($curl, $response, $cookies);
    }

    /**
     * @param $url
     * @param $params
     * @param array $headers
     * @param $channel
     * @param string $cookies
     * @param bool $log
     * @param null $proxy
     * @return string
     */
    public static function delete($url, $params, $headers = [], $channel, string &$cookies = '', bool $log = true, $proxy = null)
    {
        $curl = self::initCurlHandle(compact('url', 'headers', 'cookies'), static::getTimeout(), $proxy);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        $response = curl_exec($curl);

        if ($log) {
            self::logCurlResponse($curl, $channel, $url, $params, $response);
        }

        return self::processResponse($curl, $response, $cookies);
    }

    /**
     * Initialize a cURL handle with common options
     *
     * @param array $request
     * @param int $timeout
     * @return false|resource
     */
    private static function initCurlHandle(array $request, int $timeout, $proxy)
    {
        $curl = curl_init($request['url'] . ($request['params'] ? '?' . http_build_query($request['params']) : ''));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, static::getConnectionTimeout());
        if (!empty($request['headers'])) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $request['headers']);
        }
        if (!empty($request['cookies'])) {
            curl_setopt($curl, CURLOPT_COOKIE, $request['cookies']);
        }
        if($proxy) {
            curl_setopt($curl, CURLOPT_PROXY, $proxy);
        }


        return $curl;
    }

    /**
     * Retry cURL handle initialization with reduced timeout
     *
     * @param resource $multi_handle
     * @param resource $handle
     * @param array $request
     * @param int $timeout
     * @return resource
     */
    private static function retryCurlHandle($multi_handle, $handle, array $request, int $timeout)
    {
        curl_multi_remove_handle($multi_handle, $handle);
        curl_close($handle);
        $curl = self::initCurlHandle($request, $timeout);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_multi_add_handle($multi_handle, $curl);
        return $curl;
    }

    /**
     * Log cURL error messages
     *
     * @param string $url
     * @param string $error
     * @param int $code
     */
    private static function logCurlError(string $url, string $error, int $code)
    {
        if (!empty($error)) {
            LoggerHelper::getLogger('multi-curl')->error($url);
            LoggerHelper::getLogger('multi-curl')->error(var_export($error, true));
        }
        if ($code != 200) {
            LoggerHelper::getLogger('multi-curl')->error($url);
            LoggerHelper::getLogger('multi-curl')->error('Response code ' . $code);
        }
    }

    /**
     * Log cURL response details
     *
     * @param resource $curl
     * @param string $channel
     * @param string $url
     * @param array $params
     * @param string $response
     */
    private static function logCurlResponse($curl, string $channel, string $url, array $params, string $response)
    {
        LoggerHelper::getLogger($channel)->info("Исходящие заголовки: " . var_export(curl_getinfo($curl), true));
        LoggerHelper::getLogger($channel)->info("Параметры: " . var_export($params, true));
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = array_filter(explode("\r\n", substr($response, 0, $header_size)));
        $response_headers = static::readResponseHeaders($headers, $cookies);
        LoggerHelper::getLogger($channel)->info("Адрес: \"" . $url . "\", параметры: " . var_export($params, true));
        LoggerHelper::getLogger($channel)->info("Статус ответа: " . var_export(curl_getinfo($curl, CURLINFO_HTTP_CODE), true));
        LoggerHelper::getLogger($channel)->info("Ответ сервера: " . var_export($response, true));
        LoggerHelper::getLogger($channel)->info("Заголовки сервера: " . var_export($response_headers, true));
        LoggerHelper::getLogger($channel)->info("Ошибки: " . var_export(curl_error($curl), true));
    }

    /**
     * Process cURL response and extract headers
     *
     * @param resource $curl
     * @param string $response
     * @param string $cookies
     * @return string
     */
    private static function processResponse($curl, string $response, string &$cookies): string
    {
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = array_filter(explode("\r\n", substr($response, 0, $header_size)));
        $response_headers = static::readResponseHeaders($headers, $cookies);
        $response = substr($response, $header_size);
        curl_close($curl);
        return $response;
    }

    /**
     * Read response headers and extract cookies
     *
     * @param array $headers
     * @param string $cookies
     * @return array
     */
    public static function readResponseHeaders(array $headers, string &$cookies): array
    {
        $response_headers = [];
        foreach ($headers as $header) {
            if (strpos($header, ":")) {
                $key = mb_substr($header, 0, strpos($header, ":"));
                $value = mb_substr($header, strpos($header, ":") + 1, mb_strlen($header) - strpos($header, ":"));
                $response_headers[$key] = $value;
                if ($key === 'set-cookie') {
                    $cookies = $value;
                }
            }
        }
        return $response_headers;
    }
}
