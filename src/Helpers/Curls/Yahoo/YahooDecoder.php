<?php

namespace Common\Helpers\Curls\Yahoo;

use DateTime;
use DateTimeZone;
use Exception;
use RuntimeException;

class YahooDecoder
{
    public const HISTORICAL_DATA_HEADER_LINE = [
        'Date',
        'Open',
        'High',
        'Low',
        'Close',
        'Adj Close',
        'Volume'
    ];

    public const SEARCH_RESULT_FIELDS = [
        'exchange',
        'shortname',
        'quoteType',
        'symbol',
        'typeDisp',
    ];

    public const QUOTE_FIELDS_MAP = [
        'ask' => 'float',
        'askSize' => 'int',
        'averageDailyVolume10Day' => 'int',
        'averageDailyVolume3Month' => 'int',
        'bid' => 'float',
        'bidSize' => 'int',
        'bookValue' => 'float',
        'currency' => 'string',
        'dividendDate' => 'date',
        'earningsTimestamp' => 'date',
        'earningsTimestampStart' => 'date',
        'earningsTimestampEnd' => 'date',
        'epsForward' => 'float',
        'epsTrailingTwelveMonths' => 'float',
        'exchange' => 'string',
        'exchangeDataDelayedBy' => 'int',
        'exchangeTimezoneName' => 'string',
        'exchangeTimezoneShortName' => 'string',
        'fiftyDayAverage' => 'float',
        'fiftyDayAverageChange' => 'float',
        'fiftyDayAverageChangePercent' => 'float',
        'fiftyTwoWeekHigh' => 'float',
        'fiftyTwoWeekHighChange' => 'float',
        'fiftyTwoWeekHighChangePercent' => 'float',
        'fiftyTwoWeekLow' => 'float',
        'fiftyTwoWeekLowChange' => 'float',
        'fiftyTwoWeekLowChangePercent' => 'float',
        'financialCurrency' => 'string',
        'forwardPE' => 'float',
        'fullExchangeName' => 'string',
        'gmtOffSetMilliseconds' => 'int',
        'language' => 'string',
        'longName' => 'string',
        'market' => 'string',
        'marketCap' => 'int',
        'marketState' => 'string',
        'messageBoardId' => 'string',
        'postMarketChange' => 'float',
        'postMarketChangePercent' => 'float',
        'postMarketPrice' => 'float',
        'postMarketTime' => 'date',
        'priceHint' => 'int',
        'priceToBook' => 'float',
        'quoteSourceName' => 'string',
        'quoteType' => 'string',
        'regularMarketChange' => 'float',
        'regularMarketChangePercent' => 'float',
        'regularMarketDayHigh' => 'float',
        'regularMarketDayLow' => 'float',
        'regularMarketOpen' => 'float',
        'regularMarketPreviousClose' => 'float',
        'regularMarketPrice' => 'float',
        'regularMarketTime' => 'date',
        'regularMarketVolume' => 'int',
        'sharesOutstanding' => 'int',
        'shortName' => 'string',
        'sourceInterval' => 'int',
        'symbol' => 'string',
        'tradeable' => 'bool',
        'trailingAnnualDividendRate' => 'float',
        'trailingAnnualDividendYield' => 'float',
        'trailingPE' => 'float',
        'twoHundredDayAverage' => 'float',
        'twoHundredDayAverageChange' => 'float',
        'twoHundredDayAverageChangePercent' => 'float',
    ];

    /**
     * @param $responseBody
     * @return array|array[]
     * @throws Exception
     */
    public static function transformSearchResult($responseBody)
    {
        $decoded = json_decode($responseBody, true);

        if (!isset($decoded['quotes']) || !is_array($decoded['quotes']))
        {
            throw new RuntimeException('Yahoo Search API returned an invalid response');
        }

        $array = [];

        foreach ($decoded['quotes'] as $quote)
        {
            if(isset($quote['symbol'], $quote['shortname'], $quote['exchange'], $quote['quoteType'], $quote['typeDisp']))
            {
                $array[] = $quote;
            }
        }

        return array_map(static function ($item) {
            return self::createSearchResultFromJson($item);
        }, $array);
    }

    /**
     * @param array $json
     * @return array
     * @throws Exception
     */
    private static function createSearchResultFromJson(array $json)
    {
        $missingFields = array_diff(self::SEARCH_RESULT_FIELDS, array_keys($json));
        if ($missingFields)
        {
            throw new RuntimeException('Search result is missing fields: '. implode(', ', $missingFields) . ' - ' . implode(', ', $json));
        }

        $symbol = $json['symbol'];
        $name = $json['shortname'];
        $exch = $json['exchange'];
        $type = $json['quoteType'];
        $typeDisp = $json['typeDisp'];
        $searchTime = $json['search_time'];

        return [
            'symbol' => $symbol,
            'name' => $name,
            'exch' => $exch,
            'type' => $type,
            'type_disp' => $typeDisp,
            'search_time' => $searchTime
        ];
    }

    /**
     * @param $data
     * @return array
     * @throws Exception
     */
    public static function transformHistoricalDataResult($data)
    {
        $lines = array_map('trim', explode("\n", trim($data)));
        $headerLine = array_shift($lines);
        $expectedHeaderLine = implode(',', self::HISTORICAL_DATA_HEADER_LINE);
        if ($headerLine !== $expectedHeaderLine)
        {
            throw new RuntimeException('CSV header line did not match expected header line, given: '.$headerLine.', expected: '.$expectedHeaderLine);
        }

        return array_map(static function ($line) {
            return self::createHistoricalData(explode(',', $line));
        }, $lines);
    }

    /**
     * @param array $columns
     * @return array
     * @throws Exception
     */
    private static function createHistoricalData(array $columns)
    {
        if (7 !== count($columns)) {
            throw new RuntimeException('CSV did not contain correct number of columns');
        }

        try {
            //TODO carbon
            $date = new DateTime($columns[0], new DateTimeZone('UTC'));
        } catch (Exception $e) {
            throw new RuntimeException('Not a date in column "Date":'.$columns[0]);
        }

        for ($i = 1; $i <= 6; ++$i)
        {
            if (!is_numeric($columns[$i]) && 'null' !== $columns[$i]) {
                throw new RuntimeException('Not a number in column "'.self::HISTORICAL_DATA_HEADER_LINE[$i].'": '.$columns[$i]);
            }
        }

        $open = (float) $columns[1];
        $high = (float) $columns[2];
        $low = (float) $columns[3];
        $close = (float) $columns[4];
        $adjClose = (float) $columns[5];
        $volume = (int) $columns[6];

        return [
            'date' => $date,
            'open' => $open,
            'high' => $high,
            'low' => $low,
            'close' => $close,
            'adj_close' => $adjClose,
            'volume' => $volume
        ];
    }

    /**
     * @param $responseBody
     * @return array
     * @throws Exception
     */
    public static function transformQuotes($responseBody)
    {
        $decoded = json_decode($responseBody, true);
        if (!isset($decoded['quoteResponse']['result']) || !is_array($decoded['quoteResponse']['result'])) {
            throw new RuntimeException('Yahoo Search API returned an invalid result.');
        }

        $results = $decoded['quoteResponse']['result'];

        // Single element is returned directly in "quote"
        return array_map(static function ($item) {
            return self::createQuote($item);
        }, $results);
    }

    /**
     * @param array $json
     * @return array
     * @throws Exception
     */
    private static function createQuote(array $json)
    {
        $mappedValues = [];
        foreach ($json as $field => $value) {
            if (array_key_exists($field, self::QUOTE_FIELDS_MAP)) {
                $type = self::QUOTE_FIELDS_MAP[$field];
                $mappedValues[$field] = self::mapValue($field, $value, $type);
            }
        }

        return $mappedValues;
    }

    /**
     * @param $field
     * @param $rawValue
     * @param $type
     * @return bool|DateTime|float|int|string|null
     * @throws Exception
     */
    private static function mapValue($field, $rawValue, $type)
    {
        if (null === $rawValue) {
            return null;
        }

        switch ($type) {
            case 'float':
                return self::mapFloatValue($field, $rawValue);
            case 'percent':
                return self::mapPercentValue($field, $rawValue);
            case 'int':
                return self::mapIntValue($field, $rawValue);
            case 'date':
                return self::mapDateValue($field, $rawValue);
            case 'string':
                return (string) $rawValue;
            case 'bool':
                return self::mapBoolValue($rawValue);
            default:
                throw new RuntimeException('Invalid data type '.$type.' for field '.$field);
        }
    }

    /**
     * @param $field
     * @param $rawValue
     * @return float
     * @throws Exception
     */
    private static function mapFloatValue($field, $rawValue)
    {
        if (!is_numeric($rawValue)) {
            throw new RuntimeException('Not a number in field "'.$field.'": '.$rawValue);
        }

        return (float) $rawValue;
    }

    /**
     * @param $field
     * @param $rawValue
     * @return float
     */
    private static function mapPercentValue($field, $rawValue)
    {
        if ('%' !== substr($rawValue, -1, 1)) {
            throw new RuntimeException('Not a percent in field "'.$field.'": '.$rawValue);
        }

        $numericPart = substr($rawValue, 0, strlen($rawValue) - 1);
        if (!is_numeric($numericPart)) {
            throw new RuntimeException('Not a percent in field "'.$field.'": '.$rawValue);
        }

        return (float) $numericPart;
    }

    /**
     * @param $field
     * @param $rawValue
     * @return int
     */
    private static function mapIntValue($field, $rawValue)
    {
        if (!is_numeric($rawValue)) {
            throw new RuntimeException('Not a number in field "'.$field.'": '.$rawValue);
        }

        return (int) $rawValue;
    }

    /**
     * @param $rawValue
     * @return bool
     */
    private static function mapBoolValue($rawValue)
    {
        return (bool) $rawValue;
    }

    /**
     * @param $field
     * @param $rawValue
     * @return DateTime
     */
    private static function mapDateValue($field, $rawValue)
    {
        try {
            return new DateTime('@'.$rawValue);
        } catch (Exception $e) {
            throw new RuntimeException('Not a date in field "'.$field.'": '.$rawValue);
        }
    }
}
