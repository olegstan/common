<?php

namespace Common\Helpers\Curls;

use Common\Helpers\LoggerHelper;
use App;

class Curl
{
    //таймайут соединения для поиска с сайта
    protected const CURLOPT_CONNECTTIMEOUT = 30;
    protected const CURLOPT_TIMEOUT = 30;

    //таймаут соединения для консольных команд
    protected const COMMAND_CURLOPT_CONNECTTIMEOUT = 300;
    protected const COMMAND_CURLOPT_TIMEOUT = 300;

    /**
     * @return int
     */
    public static function getTimeout()
    {
        return static::CURLOPT_TIMEOUT;
    }

    /**
     * @return int
     */
    public static function getConnectionTimeout()
    {
        return static::CURLOPT_CONNECTTIMEOUT;
    }

    /**
     * @param $requests
     * @param int $timeout
     * @param int $max_retries
     * @return array
     */
    public static function multiGet($requests, int $timeout = 10, int $max_retries = 3): array
    {
        // инициализируем мульти-ручку и массив дескрипторов
        $multi_handle = curl_multi_init();
        $handles = array();
        foreach ($requests as $key => $request) {
            $resultUrl = $request['url'] . ($request['params'] ? '?' . http_build_query($request['params']) : '');

            $handles[$key] = curl_init($resultUrl);
            curl_setopt($handles[$key], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handles[$key], CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($handles[$key], CURLOPT_TIMEOUT, $timeout);
            curl_setopt($handles[$key], CURLOPT_MAXREDIRS, 10);
            curl_setopt($handles[$key], CURLOPT_CONNECTTIMEOUT, static::getConnectionTimeout());

            if ($request['headers']) {
                curl_setopt($handles[$key], CURLOPT_HTTPHEADER, $request['headers']);
            }
            curl_multi_add_handle($multi_handle, $handles[$key]);
        }

        $responses = array();
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

                // сохраняем ответ в массиве
                $responses[$completed_key] = $completed_response;

                if (($completed_code != 200 || !empty($error)) && $retry_count[$completed_key] < $max_retries) {
                    $resultUrl = $requests[$completed_key]['url'] . ($requests[$completed_key]['params'] ? '?' . http_build_query(
                                $requests[$completed_key]['params']
                            ) : '');

                    if (!empty($error)) {
                        LoggerHelper::getLogger('multi-curl')->error($resultUrl);
                        LoggerHelper::getLogger('multi-curl')->error(var_export($error, true));
                    }

                    if ($completed_code != 200) {
                        LoggerHelper::getLogger('multi-curl')->error($resultUrl);
                        LoggerHelper::getLogger('multi-curl')->error('Response code ' . $completed_code);
                    }

                    // повторяем запрос до достижения кода 200 или максимального количества попыток
                    curl_multi_remove_handle($multi_handle, $completed_handle['handle']);
                    curl_close($completed_handle['handle']);


                    $handles[$completed_key] = curl_init($resultUrl);
                    curl_setopt($handles[$completed_key], CURLOPT_RETURNTRANSFER, true);
                    if ($requests[$completed_key]['headers']) {
                        curl_setopt($handles[$completed_key], CURLOPT_HTTPHEADER, $requests[$completed_key]['headers']);
                    }
                    curl_setopt($handles[$completed_key], CURLOPT_TIMEOUT, $timeout);
                    curl_setopt($handles[$completed_key], CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($handles[$completed_key], CURLOPT_MAXREDIRS, 10);
                    curl_setopt($handles[$completed_key], CURLOPT_CONNECTTIMEOUT, 5);
                    curl_multi_add_handle($multi_handle, $handles[$completed_key]);
                    $retry_count[$completed_key]++;
                } else {
                    $responses[$completed_key] = object_to_array(json_decode($responses[$completed_key]));
                    $responses[$completed_key]['search_time'] = curl_getinfo($handles[$completed_key], CURLINFO_TOTAL_TIME);
                    $responses[$completed_key] = json_encode($responses[$completed_key]);
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
     * @param $params
     * @param $headers
     * @param $channel
     * @param string $cookies
     * @param bool $log
     * @return false|string
     */
    public static function get($url, $params = [], $headers = [], $channel, string &$cookies = '', bool $log = true)
    {
        $options = [];
        $url .= ($params ? '?' . http_build_query($params) : '');

        if (!empty($cookies)) {
            $headers[] = 'Cookie: ' . $cookies;
        }

        $curl = curl_init($url);
        curl_setopt_array(
            $curl,
            $options + [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_TIMEOUT => static::getTimeout(),
                CURLOPT_CONNECTTIMEOUT => static::getConnectionTimeout(),
                CURLOPT_HTTPHEADER => $headers,
                CURLINFO_HEADER_OUT => true,
            ]
        );

        $response = curl_exec($curl);

        if ($log) {
            LoggerHelper::getLogger($channel)->info("Исходящие заголовки: " . var_export(curl_getinfo($curl), true));
            LoggerHelper::getLogger($channel)->info("Параметры: " . var_export($params, true));
        }

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = array_filter(explode("\r\n", substr($response, 0, $header_size)));

        $response_headers = static::readResponseHeaders($headers, $cookies);

        $response = substr($response, $header_size);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($log) {
            LoggerHelper::getLogger($channel)->info("Адрес: \"" . $url . "\", параметры: " . var_export($params, true));
            LoggerHelper::getLogger($channel)->info("Статус ответа: " . var_export($httpcode, true));
            LoggerHelper::getLogger($channel)->info("Ответ сервера: " . var_export($response, true));
            LoggerHelper::getLogger($channel)->info("Ответ сервера json: " . var_export($response, true));
            LoggerHelper::getLogger($channel)->info("Заголовки сервера: " . var_export($response_headers, true));
            LoggerHelper::getLogger($channel)->info("Ошибки: " . var_export(curl_error($curl), true));
        }
        curl_close($curl);

        return $response;
    }

    /**
     * @param $url
     * @param $params
     * @param $headers
     * @param $channel
     * @param string $cookies
     * @param bool $log
     * @return false|string
     */
    public static function post($url, $params, $headers = [], $channel, string &$cookies = '', bool $log = true)
    {
        $options = [];
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = $params;

        if (!empty($cookies)) {
            $headers[] = 'Cookie: ' . $cookies;
        }

        $curl = curl_init($url);
        curl_setopt_array(
            $curl,
            $options + [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_TIMEOUT => static::getTimeout(),
                CURLOPT_CONNECTTIMEOUT => static::getConnectionTimeout(),
                CURLOPT_HTTPHEADER => $headers,
                CURLINFO_HEADER_OUT => true,
            ]
        );

        $response = curl_exec($curl);

        if ($log) {
            LoggerHelper::getLogger($channel)->info("Исходящие заголовки: " . var_export(curl_getinfo($curl), true));
        }
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = array_filter(explode("\r\n", substr($response, 0, $header_size)));

        $response_headers = static::readResponseHeaders($headers, $cookies);

        $response = substr($response, $header_size);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($log) {
            LoggerHelper::getLogger($channel)->info("Адрес: \"" . $url . "\", параметры: " . var_export($params, true));
            LoggerHelper::getLogger($channel)->info("Статус ответа: " . var_export($httpcode, true));
            LoggerHelper::getLogger($channel)->info("Ответ сервера: " . var_export($response, true));
            LoggerHelper::getLogger($channel)->info("Ответ сервера json: " . var_export($response, true));
            LoggerHelper::getLogger($channel)->info("Заголовки сервера: " . var_export($response_headers, true));
            LoggerHelper::getLogger($channel)->info("Ошибки: " . var_export(curl_error($curl), true));
        }
        curl_close($curl);

        return $response;
    }

    /**
     * @param $url
     * @param $params
     * @param $headers
     * @param $channel
     * @param string $cookies
     * @param bool $log
     * @return false|string
     */
    public static function put($url, $params, $headers = [], $channel, string &$cookies = '', bool $log = true)
    {
        $options = [];
        $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
        $options[CURLOPT_POSTFIELDS] = $params;

        if (!empty($cookies)) {
            $headers[] = 'Cookie: ' . $cookies;
        }

        $curl = curl_init($url);
        curl_setopt_array(
            $curl,
            $options + [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_TIMEOUT => static::getTimeout(),
                CURLOPT_CONNECTTIMEOUT => static::getConnectionTimeout(),
                CURLOPT_HTTPHEADER => $headers,
                CURLINFO_HEADER_OUT => true,
            ]
        );

        $response = curl_exec($curl);
        if ($log) {
            LoggerHelper::getLogger($channel)->info("Исходящие заголовки: " . var_export(curl_getinfo($curl), true));
        }
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = array_filter(explode("\r\n", substr($response, 0, $header_size)));

        $response_headers = static::readResponseHeaders($headers, $cookies);

        $response = substr($response, $header_size);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($log) {
            LoggerHelper::getLogger($channel)->info("Адрес: \"" . $url . "\", параметры: " . var_export($params, true));
            LoggerHelper::getLogger($channel)->info("Статус ответа: " . var_export($httpcode, true));
            LoggerHelper::getLogger($channel)->info("Ответ сервера: " . var_export($response, true));
            LoggerHelper::getLogger($channel)->info("Ответ сервера json: " . var_export($response, true));
            LoggerHelper::getLogger($channel)->info("Заголовки сервера: " . var_export($response_headers, true));
            LoggerHelper::getLogger($channel)->info("Ошибки: " . var_export(curl_error($curl), true));
        }
        curl_close($curl);

        return $response;
    }

    /**
     * @param $url
     * @param $params
     * @param $headers
     * @param $channel
     * @param string $cookies
     * @param bool $log
     * @return false|string
     */
    public static function delete($url, $params, $headers = [], $channel, string &$cookies = '', bool $log = true)
    {
        $options = [];
        $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';

        if (!empty($cookies)) {
            $headers[] = 'Cookie: ' . $cookies;
        }

        $curl = curl_init($url);
        curl_setopt_array(
            $curl,
            $options + [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_TIMEOUT => static::getTimeout(),
                CURLOPT_CONNECTTIMEOUT => static::getConnectionTimeout(),
                CURLOPT_HTTPHEADER => $headers,
                CURLINFO_HEADER_OUT => true,
            ]
        );

        $response = curl_exec($curl);
        if ($log) {
            LoggerHelper::getLogger($channel)->info("Исходящие заголовки: " . var_export(curl_getinfo($curl), true));
        }
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = array_filter(explode("\r\n", substr($response, 0, $header_size)));

        $response_headers = static::readResponseHeaders($headers, $cookies);

        $response = substr($response, $header_size);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($log) {
            LoggerHelper::getLogger($channel)->info("Адрес: \"" . $url . "\", параметры: " . var_export($params, true));
            LoggerHelper::getLogger($channel)->info("Статус ответа: " . var_export($httpcode, true));
            LoggerHelper::getLogger($channel)->info("Ответ сервера: " . var_export($response, true));
            LoggerHelper::getLogger($channel)->info("Ответ сервера json: " . var_export($response, true));
            LoggerHelper::getLogger($channel)->info("Заголовки сервера: " . var_export($response_headers, true));
            LoggerHelper::getLogger($channel)->info("Ошибки: " . var_export(curl_error($curl), true));
        }
        curl_close($curl);

        return $response;
    }

    /**
     * @param $headers
     * @param $cookies
     * @return array
     */
    public static function readResponseHeaders($headers, &$cookies)
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
            } else {
                continue;
            }
        }

        return $response_headers;
    }
}
