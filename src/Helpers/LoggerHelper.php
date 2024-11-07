<?php
namespace Common\Helpers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class LoggerHelper
{
    /**
     * @var array
     */
    public static $logQuery = true;
    /**
     * @var array
     */
    public static $loggers = [];

    /**
     * @var string|bool
     */
    public static $commandKey = false;
    /**
     * @var string|bool
     */
    public static $jobKey = false;
    /**
     * @var string|bool
     */
    public static $testKey = false;

    /**
     * @var bool
     */
    public static $removeDefaultHandler = true;

    /**
     * @param string $key
     * @return Logger
     */
    public static function getLogger($key = 'laravel')
    {
        if(!isset(self::$loggers[$key]))
        {
            /**
             * @var Logger $monolog
             */
            if($key === 'laravel'){
                $monolog = Log::getLogger();

                if(self::$removeDefaultHandler){
                    $handlers = $monolog->getHandlers();

                    foreach ($handlers as $handler){
                        $monolog->popHandler();
                    }

                    self::$removeDefaultHandler = false;
                }
            }else{
                $monolog = new ExtendLogger($key);
            }

            if(self::$commandKey)
            {
                //определяем путь куда писать логи
                switch (self::$commandKey)
                {
                    case 'queue':
                        $path = storage_path('logs/queue/' . self::prepareCommandKey(self::$jobKey));
                        break;
                    case 'tests':
                        $path = storage_path('logs/tests/' . self::prepareCommandKey(self::$testKey));
                        break;
                    default:
                        $path = storage_path('logs/commands/' . self::prepareCommandKey(self::$commandKey));
                        break;
                }

                if(!File::exists($path)){
                    File::makeDirectory($path, 0777, true, true);
                }
                $filename = $path . '/' . $key . '.log';
            }else{
                $url = Request::fullUrl();

                if(str_contains($url, 'api/v1/call'))
                {
                    $partsBeforeGetParams = explode( '?', $url);
                    $parts = explode( '/', $partsBeforeGetParams[0] ?? '');
                    $partsLength = count($parts);


                    if(isset($parts[$partsLength - 1]) && isset($parts[$partsLength - 2]))
                    {
                        $controllerName = $parts[$partsLength - 2];
                        $methodName = $parts[$partsLength - 1];

                        $path = storage_path('logs/front/' . $controllerName . '/' . $methodName);
                        if(!File::exists($path)) {
                            File::makeDirectory($path, 0777, true, true);
                        }

                        $filename = $path . '/' . $key . '.log';
                    }
                }else{

                    $path = storage_path('logs/common');
                    if(!File::exists($path)) {
                        File::makeDirectory($path, 0777, true, true);
                    }

                    $filename = $path . '/' . $key . '.log';
                }
            }
            self::setHandler($monolog, $filename);

            self::$loggers[$key] = $monolog;
        }
        return self::$loggers[$key];
    }

    /**
     * @param Logger $monolog
     * @param $filename
     */
    public static function setHandler($monolog, $filename)
    {
        $handler = new RotatingFileHandler($filename, 10, Logger::DEBUG, true, 0777);
        $handler->setFormatter(new LineFormatter(null, 'Y-m-d H:i:s', true, true));
        $monolog->pushHandler($handler);
    }

    /**
     * @param $key
     * @return null|string|string[]
     */
    public static function prepareCommandKey($key)
    {
        $key = str_replace(' ', '-', $key);

        return preg_replace('/[^A-Za-z0-9\-]/', '', $key);
    }

    /**
     * @param $sql
     * @param null $hash
     */
    public static function listenQuery($sql, $hash = null)
    {
        /**
         * @var QueryExecuted $sql
         */
        if($sql->time > 100){
            $key = 'slow-query';
        }else{
            $key = 'query';
        }

        $sqlWithBindings = $sql->sql;

        foreach ($sql->bindings as $binding) {
            $value = is_numeric($binding) ? $binding : "'" . $binding . "'";
            $sqlWithBindings = preg_replace('/\?/', $value, $sqlWithBindings, 1);
        }

        LoggerHelper::getLogger($key)->debug(
            'SQL => ' . $sqlWithBindings . PHP_EOL .
            'TIME => ' . $sql->time . ' milliseconds' . PHP_EOL .
            ($hash ? ('HASH => ' . $hash) : '')
        );
    }
}
