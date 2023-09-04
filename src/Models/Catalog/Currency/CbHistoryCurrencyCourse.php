<?php

namespace Common\Models\Catalog\Currency;

use Common\Helpers\Curls\Currency\CbCurl;
use Common\Helpers\LoggerHelper;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Currency;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Cache;

/**
 * @property $currency_id
 * @property $value
 * @property $nominal
 * @property $date
 */
class CbHistoryCurrencyCourse extends BaseCatalog
{
    /**
     * @var string
     */
    public $table = 'cb_history_currency_courses';

    /**
     * @var array
     */
    protected $fillable = [
        'currency_id',
        'value',
        'nominal',
        'date',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'currency_id' => 'integer',
        'value' => 'float',
        'nominal' => 'float',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    public static array $codes = [
        Currency::EUR,
        Currency::USD
    ];

    /**
     * @return HasOne
     */
    public function cb_currency(): HasOne
    {
        return $this->hasOne(CbCurrency::class, 'id', 'currency_id');
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @param CbCurrency $currency
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return bool
     */
    public static function loadHistory(CbCurrency $currency, Carbon $startDate, Carbon $endDate): bool
    {
        $key = $startDate->format('Y-m-d') . ' / ' . $endDate->format('Y-m-d');

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $dataRows = CbCurl::getCourses($currency->cb_id, $startDate, $endDate);

        if (count($dataRows)) {
            Cache::add($key, true, Carbon::now()->addDay());

            foreach ($dataRows as $row) {
                $date = Carbon::createFromFormat('d.m.Y', (string)$row->attributes()->Date[0]);

                $cbCurrencyHistory = self::where('currency_id', $currency->id)
                    ->where('date', $date->format('Y-m-d'))
                    ->first();

                if (!$cbCurrencyHistory) {
                    self::create([
                        'currency_id' => $currency->id,
                        'value' => (float)str_replace(',', '.', $row->Value),
                        'nominal' => (float)str_replace(',', '.', $row->Nominal),
                        'date' => $date->format('Y-m-d'),
                    ]);
                }
            }

            return true;
        }

        Cache::add($key, false, Carbon::now()->addDay());
        LoggerHelper::getLogger()->info('No any history for ' . $currency->char_code);

        return false;
    }

    /**
     * @param $query
     * @param CbCurrency $item
     * @return void
     */
    public function scopeByItem($query, CbCurrency $item)
    {
        $query->where('currency_id', $item->id);
    }
}
