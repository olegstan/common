<?php

namespace Common\Helpers\Curls\TradingView;

use Common\Helpers\PythonScript\PythonScriptTvCurl;
use Common\Helpers\TradingViewWebsocket;
use Common\Models\Catalog\TradingView\TradingViewKey;
use Common\Models\Catalog\TradingView\TradingViewQuarter;
use Common\Models\Catalog\TradingView\TradingViewTicker;
use Common\Models\Catalog\TradingView\TradingViewYear;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Common\Helpers\LoggerHelper;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TradingViewCurl
{
    public static array $countries = [
        'russia',
        'america',
        'argentina',
        'australia',
        'bahrain',
        'belgium',
        'brazil',
        'canada',
        'chile',
        'china',
        'colombia',
        'denmark',
        'egypt',
        'estonia',
        'finland',
        'france',
        'germany',
        'greece',
        'hongkong',
        'iceland',
        'india',
        'indonesia',
        'israel',
        'italy',
        'japan',
        'latvia',
        'lithuania',
        'malaysia',
        'mexico',
        'netherlands',
        'newzealand',
        'nigeria',
        'norway',
        'peru',
        'philippines',
        'poland',
        'portugal',
        'qatar',
        'ksa',
        'serbia',
        'singapore',
        'slovakia',
        'rsa',
        'korea',
        'spain',
        'sweden',
        'switzerland',
        'taiwan',
        'thailand',
        'turkey',
        'uae',
        'uk',
        'vietnam',
    ];

    /**
     * @param $result
     * @param int $key
     * @return mixed|void
     */
    public static function saveImage($result, int $key = 13)
    {
        try {
            if (is_object($result) &&
                $result->totalCount === 1 &&
                isset($result->data[0]->d[$key]) &&
                !empty($result->data[0]->d[$key])) {
                //Получаем имя иконки из json
                $nameImage = $result->data[0]->d[$key];
                //определяем директорию, куда будет сохранена
                $path = base_path() . '/public/images/icons/' . $nameImage . '.svg';
                //Составляем url по которой будем получать ссылку иконки
                $urlImage = 'https://s3-symbol-logo.tradingview.com/' . $nameImage . '.svg';

                if ($nameImage) {
                    //Сохраняем картинку у нас
                    $curl = curl_init($urlImage);
                    curl_setopt($curl, CURLOPT_HEADER, 0);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    $content = curl_exec($curl);
                    curl_close($curl);
                    file_exists($path) && unlink($path);
                    $fp = fopen($path, 'xb');

                    if ($fp) {
                        fwrite($fp, $content);
                        fclose($fp);
                        return $nameImage;
                    }
                }
            }
        } catch (Exception $e) {
            LoggerHelper::getLogger('save_image')->error($e);
        }
    }

    /**
     * @param $func
     * @param $ticker
     * @return void
     */
    public static function parseData($func, $ticker): void
    {
        try {
            TradingViewWebsocket::create($func, $ticker);
        } catch (Exception $e) {
            LoggerHelper::getLogger('websocket-parseData')->error($e);
        }
    }

    /**
     * @param $obj
     * @param $func
     * @param $ticker
     * @return void
     */
    public static function readQuotes($obj, $func, $ticker): void
    {
        try {
            //из TradingViewWebsocket приходит портянка и перебираем ее
            foreach ($obj->tickerData as $datas) {
                //обязательный параметр, тк от него потом ищутся графики
//                if (isset($datas['listed_exchange'])) {
                if (isset($datas)) {
                    //апдейтим инфу и подгружаем данные по годам + удаляем тикер из массива, если он таковым является
                    switch ($func) {
                        //запускается из файла TradingViewCommand
                        case 'symbol':
                            self::symbolInfo($datas, $ticker);

                            break;
                        //запускается из файла TradingViewQartalAndYearCommand
                        case 'history':
                            self::createQuarterlyNYear($datas);

                            break;

                        case 'cap':
                            if (isset($datas['market_cap_basic'])) {
                                self::updateMarketCup($datas, $ticker);
                            }

                            break;
                    }

                    if (is_array($obj->ticker)) {
                        foreach ($obj->ticker as $k => $value) {
                            if ($value === $ticker) {
                                unset($obj->ticker[$k]);
                            }
                        }
                    }

                    //если массив с тикерами и он пустой или передан 1 тикер в виде текста, закрывам соединение
                    if ((is_array($obj->ticker) && empty($obj->ticker)) || !is_array($obj->ticker)) {
                        $obj->closeSocket = true;
                    }
                }
            }
        } catch (Exception $e) {
            LoggerHelper::getLogger('websocket-readQuotes')->error($e);
        }
    }

    /**
     * @param $datas
     * @param $ticker
     * @return void
     */
    public static function updateMarketCup($datas, $ticker): void
    {
        //ищем тикер который надо апдейтить
        $ticker = TradingViewTicker::where('symbol', $ticker)
            ->where('description', $datas['description'])
            ->first();

        if ($ticker) {
            //еще есть ключ market_cap_basic_current
            //как я понял, это округленное значение капитала (или фиксированное на день)
            $ticker->capitalization = $datas['market_cap_basic'];
            $ticker->save();
        }
    }

    /**
     * @param $datas
     * @param $symbol
     * @return void
     */
    public static function symbolInfo($datas, $symbol): void
    {
        //ищем тикер который надо апдейтить
        $ticker = TradingViewTicker::where('symbol', $symbol)
            ->where('description', $datas['description'])
            ->first();

        if ($ticker) {
            //начальный параметр валюты
            $currency = null;

            //если при записи тикера не было валюты, но есть при апдейте, ставим из апдейта
            if (is_null($ticker->currency) && isset($datas['currency'])) {
                $currency = $datas['currency'];
            } //если валюты уже стояла при создании, проставляем ее, тк у апдейта может стоять null
            elseif ($ticker->currency) {
                $currency = $ticker->currency;
            }

            try {
                //апдейтим поля которые можем
                $ticker->update([
                    'type' => $datas['type'] ?? null,
                    'point_value' => $datas['pointvalue'] ?? null,
                    'exchange_web' => $datas['exchange'] ?? null,
                    'listed_exchange' => $datas['listed_exchange'],
                    'currency' => $currency,
                    'tick_size' => $datas['variable_tick_size'] ?? null,
                    'sector' => $datas['sector'] ?? null,
                    'ru_sector' => isset($datas['sector']) ? GoogleTranslate::trans(
                        $datas['sector'],
                        'ru',
                        'en'
                    ) : null,
                    'industry' => $datas['industry'] ?? null,
                    'ru_industry' => isset($datas['sector']) ? GoogleTranslate::trans(
                        $datas['industry'],
                        'ru',
                        'en'
                    ) : null,
                    'timezone' => $datas['timezone'] ?? null,
                    'session' => isset($datas['subsessions']) ? json_encode($datas['subsessions']) : null,
                    'capitalization' => $datas['market_cap_basic'] ?? null,
                    'average_volume' => $datas['average_volume'] ?? null,
                    'is_parse' => true,
                ]);

                //у текущего тикера могут быть дочерние тикеры, у них так же надо обновить нужные поля
                $childTickers = TradingViewTicker::where('parent_id', $ticker->id)->get();

                //убеждаемся что такие есть
                if ($childTickers) {
                    //и перебираем их
                    foreach ($childTickers as $childTicker) {
                        try {
                            $childTicker->update([
                                'type' => $datas['type'] ?? null,
                                'exchange_web' => $datas['exchange'] ?? null,
                                'listed_exchange' => $datas['listed_exchange'],
                                'currency' => $currency,
                                'capitalization' => $datas['market_cap_basic'] ?? null,
                                'average_volume' => $datas['average_volume'] ?? null,
                                'is_parse' => true,
                            ]);
                        } catch (Exception $e) {
                            LoggerHelper::getLogger('child-tradingview')->error($e);
                            LoggerHelper::getLogger('child-tradingview')->error(
                                var_export($datas, true)
                            );
                        }
                    }
                }
            } catch (Exception $e) {
                LoggerHelper::getLogger('parent-tradingview')->error($e);
                LoggerHelper::getLogger('parent-tradingview')->error(
                    var_export($datas, true)
                );
            }
        }
    }

    /**
     * @param $datas
     * @return void
     */
    public static function createQuarterlyNYear($datas): void
    {
        try {
            $tickerId = TradingViewTicker::where('symbol', $datas['symbol'])
                ->where('description', $datas['description'])
                ->first();

            foreach ($datas as $key => $data) {
                $tradingViewKey = TradingViewKey::where('key', $key)->value('id');
                $date = Carbon::now();

                if ($tradingViewKey && $tickerId) {
                    //Кварталы
                    if (strpos($key, '_fq')) {
                        self::quarter($data, $date, $tradingViewKey, $tickerId);
                    }
                    //Года
                    if (strpos($key, '_fy')) {
                        self::year($data, $date, $tradingViewKey, $tickerId);
                    }
                    //ttm - вроде бы текущий момент
                    if (strpos($key, '_ttm')) {
                        self::ttm($key, $tickerId, $data);
                    }
                }
            }

            $tickerId->update_history = Carbon::today();
            $tickerId->save();
        } catch (Exception $e) {
            LoggerHelper::getLogger('tradingview')->error(var_export($datas, true));
            LoggerHelper::getLogger('tradingview')->error($e);
        }
    }

    /**
     * @param $data
     * @param $date
     * @param $tradingViewKey
     * @param $tickerId
     * @return void
     */
    public static function quarter($data, $date, $tradingViewKey, $tickerId): void
    {
        $date->subMonths(3);

        if (is_array($data)) {
            $lastQuarter = $date;

            foreach ($data as $datum) {
                $quarterAll = TradingViewQuarter::where('key_id', $tradingViewKey)
                    ->where('ticker_id', $tickerId->id)
                    ->where('year', (string)$lastQuarter->year)
                    ->where('value', $datum)
                    ->where('quarter', $lastQuarter->quarter)
                    ->first();

                if (!$quarterAll) {
                    TradingViewQuarter::create([
                        'key_id' => $tradingViewKey,
                        'year' => (string)$lastQuarter->year,
                        'quarter' => $lastQuarter->quarter,
                        'value' => $datum,
                        'ticker_id' => $tickerId->id,
                    ]);

                    $lastQuarter = $lastQuarter->subQuarter();
                }
            }
            //вроде бы если не массив, то показывает на текущий момент
        } else {
            $quarterOne = TradingViewQuarter::where('key_id', $tradingViewKey)
                ->where('ticker_id', $tickerId->id)
                ->where('year', $date->format('Y'))
                ->where('value', $data)
                ->where('quarter', $date->quarter)
                ->first();

            if (!$quarterOne) {
                TradingViewQuarter::create([
                    'key_id' => $tradingViewKey,
                    'year' => $date->format('Y'),
                    'quarter' => $date->quarter,
                    'value' => $data,
                    'ticker_id' => $tickerId->id,
                ]);
            }
        }
    }

    /**
     * @param $data
     * @param $date
     * @param $tradingViewKey
     * @param $tickerId
     * @return void
     */
    public static function year($data, $date, $tradingViewKey, $tickerId): void
    {
        $startYear = Carbon::today()->subYear()->startOfYear();

        if (is_array($data)) {
            foreach ($data as $datum) {
                $yearAll = TradingViewYear::where('key_id', $tradingViewKey)
                    ->whereDate('year', $startYear->format('Y-m-d'))
                    ->where('ticker_id', $tickerId->id)
                    ->first();

                if (!$yearAll) {
                    TradingViewYear::create([
                        'key_id' => $tradingViewKey,
                        'year' => $startYear,
                        'value' => $datum,
                        'ticker_id' => $tickerId->id,
                    ]);

                    $startYear->subYear();
                } elseif ($yearAll->value !== $datum) {
                    $yearAll->value = $datum;
                    $yearAll->save();
                }
            }
            //вроде бы если не массив, то показывает на текущий момент
        } else {
            $yearOne = TradingViewYear::where('key_id', $tradingViewKey)
                ->whereDate('year', $date->format('Y-m-d'))
                ->where('ticker_id', $tickerId->id)
                ->first();

            if (!$yearOne) {
                TradingViewYear::create([
                    'key_id' => $tradingViewKey,
                    'year' => $date,
                    'value' => $data,
                    'ticker_id' => $tickerId->id,
                ]);
            }
        }
    }

    /**
     * @param $key
     * @param $tickerId
     * @param $data
     * @return void
     */
    public static function ttm($key, $tickerId, $data): void
    {
        $tradingViewKey = TradingViewKey::where('key', str_replace('_ttm', '_fy_h', $key))
            ->first();

        if ($tradingViewKey) {
            $ttm = TradingViewYear::where('key_id', $tradingViewKey->id)
                ->where('ticker_id', $tickerId->id)
                ->whereNull('year')
                ->first();

            if (!$ttm) {
                TradingViewYear::create([
                    'key_id' => $tradingViewKey->id,
                    'value' => $data,
                    'ticker_id' => $tickerId->id,
                ]);
            }
        }
    }

    /**
     * @param $ticker
     * @return void
     */
    public static function saveImageMoscowStock($ticker): void
    {
        $nameImage = self::stocksScan($ticker->secid, 'russia', 'MOEX:');

        if ($nameImage) {
            //Записываем имя иконки в бд
            $ticker->icons = $nameImage;
            $ticker->save();
        }
    }

    /**
     * @param $ticker
     * @return void
     */
    public static function saveImageYahooStock($ticker): void
    {
        $exch_disps = [
            'NASDAQ:',
            'NYSE:',
            'NYSE ARCA:',
            'OTC:',
        ];

        foreach ($exch_disps as $exch_disp) {
            $nameImage = self::stocksScan($ticker->symbol, 'america', $exch_disp);

            if ($nameImage) {
                //Записываем имя иконки в бд
                $ticker->icons = $nameImage;
                $ticker->save();
            }
        }
    }

    /**
     * @param $ticker
     * @param $country
     * @param $exch
     * @return false|mixed|null
     */
    public static function stocksScan($ticker, $country, $exch)
    {
        try {
            $rUrl = 'https://scanner.tradingview.com/' . $country . '/scan';

            $ch = curl_init($rUrl);

            $payload = '{
                "columns":[
                "price_52_week_high",
                "price_52_week_low",
                "sector","country",
                "Low.1M","High.1M",
                "Perf.W","Perf.1M",
                "Perf.3M","Perf.6M",
                "Perf.Y","Perf.YTD",
                "Recommend.All",
                "logoid",
                "currency_logoid",
                "base_currency_logoid",
                "average_volume_10d_calc"],
                "range":[0,1],
                "symbols":{"tickers":["' . $exch . self::tickersExplode($ticker) . '"]}
            }';

            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);
            $result = json_decode($json);
            curl_close($ch);

            return self::saveImage($result);
        } catch (Exception $e) {
            LoggerHelper::getLogger('save_image')->error($e);
            return false;
        }
    }

    /**
     * @param $item
     * @return TradingViewTicker|false|Model
     */
    public static function createTicker($item)
    {
        //до текущей версии брали напрямую из сокета, там были лишние символы. На всякий случай лучше оставить.
        $symbol = trim(strip_tags($item->symbol));
        $description = trim(strip_tags($item->description));

        try {
            return TradingViewTicker::create([
                'symbol' => $symbol,
                'description' => $description ?? null,
                'exchange' => $item->exchange ?? null,
                'provider_id' => $item->provider_id ?? null,
                'country' => $item->country ?? null,
                'type' => $item->type ?? null,
                'currency' => $item->currency_code ?? null,
                'typespecs' => isset($item->typespecs) ? json_encode($item->typespecs) : null,
                'icon' => $item->logoid ?? null,
            ]);
        } catch (Exception $e) {
            LoggerHelper::getLogger('tradingview-error-create-ticker')->error($e);
            return false;
        }
    }

    /**
     * @param $ticker
     * @param string $exchange
     * @return void
     */
    public static function createTickers($ticker, string $exchange = 'all'): void
    {
        //скрипт питона который возвращает массив тикеров на TradingView
        $pythonTickers = PythonScriptTvCurl::searchSymbols($ticker, $exchange);

        //убеждаемся что тикеры вообще есть
        if ($pythonTickers) {
            //перебираем все тикеры
            foreach ($pythonTickers as $pythonTicker) {
                try {
                    //запрашиваем тикер в бд с таким же символом для проверки его наличия.
                    $ticker = TradingViewTicker::where('symbol', $pythonTicker->symbol)
                        ->where('description', $pythonTicker->description)
                        ->first();

                    //если такого тикера еще нет, записываем в БД
                    if (!$ticker) {
                        $ticker = self::createTicker($pythonTicker);

                        if ($ticker) {
                            TradingViewCurl::parseData('symbol', $ticker->symbol);
                            $ticker->is_parse = true;
                            $ticker->save();
                        } else {
                            LoggerHelper::getLogger('tradingview-create-tickers')->error('Error creating the ticker');
                        }
                    }

                    //у futures могут быть контракты (в TV выглядит как вложенный список)
                    if ($ticker && isset($pythonTicker->contracts)) {
                        //таких может быть несколько, так что записываем как отдельные тикеры, ведь родительский тикер нельзя искать по графику
                        foreach ($pythonTicker->contracts as $contract) {
                            $childrenTicker = TradingViewTicker::where('symbol', $contract->symbol)
                                ->where(function ($query) use ($contract) {
                                    if (isset($contract->description)) {
                                        $query->where('description', $contract->description);
                                    }
                                })
                                ->first();

                            if (!$childrenTicker) {
                                //в массиве контрактов могут быть минимум 2 ключа (symbol, description), так что остальные берем от родителя
                                $childrenTicker = TradingViewTicker::create([
                                    'symbol' => $contract->symbol,
                                    'description' => $contract->description ?? '',
                                    'type' => $ticker->type ?? null,
                                    'provider_id' => $ticker->provider_id,
                                    'exchange' => $ticker->exchange,
                                    'currency' => $ticker->currency_code,
                                    'country' => $ticker->country,
                                    'typespecs' => isset($contract->typespecs) ? json_encode(
                                        $contract->typespecs
                                    ) : null,
                                    'parent_id' => $ticker->id,
                                ]);

                                if ($childrenTicker) {
                                    TradingViewCurl::parseData('symbol', $childrenTicker->symbol);
                                    $childrenTicker->is_parse = true;
                                    $childrenTicker->save();
                                } else {
                                    LoggerHelper::getLogger('tradingview-create-tickers')->error(
                                        'Error creating the childrenTicker'
                                    );
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    LoggerHelper::getLogger('tradingview-create-tickers')->error($e);
                }
            }
        }
    }

    /**
     * @param $text
     * @return false|mixed
     */
    public static function searchTicker($text)
    {
        try {
            $text = self::tickersExplode($text);

            $aUrl = 'https://symbol-search.tradingview.com/symbol_search/?text=' . $text . '&hl=1&exchange=&lang=en&domain=production';
            $ch = curl_init($aUrl);

            $payload = '{
                text:' . $text . ',
                hl: 1,
                exchange:,
                lang: en,
                domain: production,
            }';

            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $data = json_decode($response);
            curl_close($ch);

            return $data;
        } catch (Exception $e) {
            LoggerHelper::getLogger('tradingview')->error($e);
            return false;
        }
    }

    /**
     * @param $ticker
     * @return false|mixed|string|string[]
     */
    public static function tickersExplode($ticker)
    {
        $symbols = ['=', '-', ',', '.'];

        foreach ($symbols as $symbol) {
            $pos = strripos($ticker, $symbol);

            if ($pos) {
                $ticker = explode($symbol, $ticker);

                if (isset($ticker[0])) {
                    return $ticker[0];
                }
            }
        }

        return $ticker;
    }
}
