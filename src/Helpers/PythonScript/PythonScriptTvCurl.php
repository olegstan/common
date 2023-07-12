<?php

namespace Common\Helpers\PythonScript;

use Common\Helpers\Curls\TradingView\TradingViewCurl;
use Exception;
use Common\Helpers\LoggerHelper;

class PythonScriptTvCurl
{
    public static function searchSymbols($ticker, $exchange)
    {
        try {
            $scriptPath = base_path() . DIRECTORY_SEPARATOR . 'python' . DIRECTORY_SEPARATOR . 'tvDatafeed' . DIRECTORY_SEPARATOR .'tv_search_symbol.py';

            $command = 'python3';

            if (PHP_OS === 'WINNT') {
                $command = 'python';
            }

            $text = TradingViewCurl::tickersExplode($ticker);

            $outputString = shell_exec("$command -W ignore $scriptPath $text $exchange");
            $outputString = str_replace(array("'", "True", "False"), array("\"", "true", "false"), $outputString);

            return json_decode($outputString);
        } catch (Exception $e) {
            LoggerHelper::getLogger('PythonScriptTvCurl')->error($e);

            return false;
        }
    }
}