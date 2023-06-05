<?php

namespace Common\Helpers\PythonScript;

class PatternScripts
{
    /**
     * @param $params
     * @return mixed
     */
    public static function output($params)
    {
        $command = 'python3';

        if (PHP_OS === 'WINNT') {
            $command = 'python';
        }

        //все данные приходят из массива, а его в shell_exec не запихнуть. Сделаем из значений массива строку
        $dataString = implode(' ', $params);

        $outputString = shell_exec("$command -W ignore $dataString");

        $outputString = str_replace(
            array("'", "True", "False", "[*********************100%***********************]  1 of 1 completed"),
            array("\"", "true", "false", ""),
            $outputString
        );

        return json_decode($outputString) ?: json_decode(str_replace('"s ', '\'s ', $outputString));
    }
}