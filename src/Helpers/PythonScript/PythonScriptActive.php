<?php

namespace Common\Helpers\PythonScript;

use Common\Helpers\Helper;
use Common\Helpers\LoggerHelper;
use Exception;

class PythonScriptActive
{
    /**
     * @param $scriptPath
     * @param $count
     * @param $exchange
     * @param $time
     * @param $symbol
     *
     * @return array|false|object|null
     */
    public static function closePricedMoex($scriptPath, $count, $exchange, $time, $symbol)
    {
        try {
            //Обязательно в такой последовательности
            $params = [
                'path'     => $scriptPath,
                'symbol'   => $symbol,
                'exchange' => $exchange,
                'count'    => $count,
                'time'     => $time,
            ];

            //возвращается с питона все в виде объекта
            return Helper::object_to_array(PatternScripts::output($params));
        } catch (Exception $e) {
            LoggerHelper::getLogger('active-moex-exchange_valuation')->error($e);

            return false;
        }
    }

    /**
     * @param $scriptPath
     * @param $symbol
     * @param $method
     * @param $time
     * @param $interval
     * @return array|false|mixed
     */
    public static function closePriceYahoo($scriptPath, $symbol, $method, $time, $interval)
    {
        try {
            //Обязательно в такой последовательности
            $params = [
                'path'     => $scriptPath,
                'symbol'   => $symbol,
                'method' => $method,
                'period' => $time,
                'interval' => $interval
            ];

            //возвращается с питона все в виде объекта
            return Helper::object_to_array(PatternScripts::output($params));
        } catch (Exception $e) {
            LoggerHelper::getLogger('active-yahoo-exchange_valuation')->error($e);

            return false;
        }
    }

    /**
     * @param $scriptPath
     * @param $symbol
     * @param $exchange
     * @return array|false|mixed
     */
    public static function searchSymbolTv($scriptPath, $symbol, $exchange = null)
    {
        try {
            //Обязательно в такой последовательности
            $params = [
                'path' => $scriptPath,
                'symbol' => $symbol,
                'exchange' => $exchange,
            ];

            //возвращается с питона все в виде объекта
            return Helper::object_to_array(PatternScripts::output($params));
        } catch (Exception $e) {
            LoggerHelper::getLogger('active-yahoo-exchange_valuation')->error($e);

            return false;
        }
    }

    /**
     * @param $filePath
     * @return false|mixed
     */
    public static function updProfitability($filePath)
    {
        try {
            $scriptPath = config('roboadvisor.path');

            //Обязательно в такой последовательности
            $params = [
                'path' => $scriptPath,
                'flag' => '--path_to_json',
                'file_path' => $filePath,
            ];

            //возвращается с питона все в виде объекта
            return PatternScripts::output($params);
        } catch (Exception $e) {
            LoggerHelper::getLogger('active-yahoo-exchange_valuation')->error($e);

            return false;
        }
    }
}