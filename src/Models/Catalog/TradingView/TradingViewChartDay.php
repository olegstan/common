<?php

namespace Common\Models\Catalog\TradingView;

use Carbon\Carbon;
use Common\Helpers\LoggerHelper;
use Common\Helpers\PythonScript\PatternScripts;
use Common\Models\Catalog\BaseCatalog;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property $ticker_id
 * @property $date_at
 * @property $open
 * @property $high
 * @property $low
 * @property $close
 * @property $volume
 */
class TradingViewChartDay extends BaseCatalog
{
    /**
     * @var string
     */
    public $table = 'tv_chart_days';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ticker_id',
        'date_at',
        'open',
        'high',
        'low',
        'close',
        'volume',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'ticker_id' => 'integer',
        'date_at' => 'datetime',
        'open' => 'double',
        'high' => 'double',
        'low' => 'double',
        'close' => 'double',
        'volume' => 'double',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return HasOne
     */
    public function ticker() : HasOne
    {
        return $this->hasOne(TradingViewTicker::class, 'id', 'ticker_id');
    }

    /**
     * @param $symbol
     * @return void
     */
    public static function createChartDays($symbol): void
    {
        $scriptPath = base_path() . DIRECTORY_SEPARATOR . 'python' . DIRECTORY_SEPARATOR . 'tv_chart.py';

        $tickers = TradingViewTicker::where('symbol', $symbol)->get();

        foreach ($tickers as $ticker)
        {
            //обязательно наличие одного из полей, так что сразу отсеиваем где нету
            if ($ticker->exchange || $ticker->listed_exchange)
            {
                //указываем сразу биржу
                $exchange = $ticker->exchange;

                //биржа может состоять из 2 слов и такую не найдет, тогда надо использовать биржу другого поля
                $exchanges = explode(' ', $exchange);

                if (count($exchanges) > 1)
                {
                    $exchange = $ticker->listed_exchange ?? $exchanges[0];
                }

                //ищем запись за вчерашний день
                //если найдутся, значит уже проходились парсером по ней
                $chartDay = self::where('ticker_id', $ticker->id)
                    ->where('date_at', Carbon::now()->subDay())
                    ->first();

                //5000 - максимальное количество которое либра может предоставить
                if (!$chartDay) {
                    $count = 5000;
                } else {
                    //если у нас уже есть за прошлый день, незачем 5к записей запрашивать
                    $count = 2;
                }

                //шаблон для питон скриптов
                $arrays = object_to_array(
                    PatternScripts::output([$scriptPath, $ticker->symbol, $exchange, $count, 'day'])
                );

                if (isset($arrays))
                {
                    //массив у которого ключи это дата в unix
                    foreach ($arrays as $k => $array)
                    {
                        try
                        {
                            //конвертируем в нормальную дату, тк ключ конвертирован в unix
                            $date = Carbon::createFromTimestampUTC(substr($k, 0, -3))->format('Y-m-d H:i:s');

                            //проверяем, есть ли уже за эту дату история
                            $history = self::whereDate('date_at', $date)
                                ->where('ticker_id', $ticker->id)
                                ->first();

                            if (!$history)
                            {
                                //записываем айдишник тикера с датой в массив и удаляем что не нужно для создания записи
                                $array['ticker_id'] = $ticker->id;
                                $array['date_at'] = $date;
                                unset($array['symbol']);

                                self::create($array);
                            }

                        } catch (Exception $e) {
                                LoggerHelper::getLogger('tradingview:chart-day')->error($e);
                        }
                    }
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getClose()
    {
        return $this->close;
    }

    /**
     * @return mixed
     */
    public function getOpen()
    {
        return $this->open;
    }

    /**
     * @return mixed
     */
    public function getHigh()
    {
        return $this->high;
    }

    /**
     * @return mixed
     */
    public function getLow()
    {
        return $this->low;
    }

    /**
     * @return mixed
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return $this->date_at;
    }
}
