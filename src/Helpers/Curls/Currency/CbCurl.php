<?php

namespace Common\Helpers\Curls\Currency;

use Common\Helpers\Curls\Curl;
use Carbon\Carbon;
use Exception;
use SimpleXMLElement;
use Common\Helpers\LoggerHelper;

class CbCurl
{
    /**
     * @return SimpleXMLElement
     */
    public static function getList(): SimpleXMLElement
    {
        try {
            $data = Curl::get('http://www.cbr.ru/scripts/XML_daily.asp', [], [], 'cb');
            return new SimpleXMLElement($data);
        } catch (Exception $e) {
            LoggerHelper::getLogger('cb')->error($e);
            return self::getList();
        }
    }

    /**
     * @param $code
     * @param Carbon $dateStart
     * @param Carbon $dateEnd
     * @return SimpleXMLElement
     */
    public static function getCourses($code, Carbon $dateStart, Carbon $dateEnd): SimpleXMLElement
    {
        $params = [
            'date_req1' => $dateStart->format('d/m/Y'),
            'date_req2' => $dateEnd->format('d/m/Y'),
            'VAL_NM_RQ' => $code,
        ];

        try {
            $data = Curl::get('http://www.cbr.ru/scripts/XML_dynamic.asp', $params, [], 'cb');
            return new SimpleXMLElement($data);
        } catch (Exception $e) {
            LoggerHelper::getLogger('cb')->error($e);
            return self::getCourses($code, $dateStart, $dateEnd);
        }
    }
}
