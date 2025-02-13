<?php

namespace Common\Helpers;

use Carbon\Carbon;
use Common\Helpers\Curls\MoscowExchange\MoscowExchangeCurl;
use Common\Models\Catalog\Currency\CbCurrency;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeHistory;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeStock;
use Common\Models\Interfaces\Catalog\CommonsFuncCatalogInterface;

class HistoryHelper
{
    /**
     * @param CommonsFuncCatalogInterface|MoscowExchangeStock|CbCurrency $stock
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param false $forceSkipCache
     * @return bool
     */
    public static function loadHistory(CommonsFuncCatalogInterface $stock, Carbon $startDate, Carbon $endDate, $forceSkipCache = false)
    {
        $stockCacheKey = $stock->getStockCacheKey();

        // 2. Загружаем текущее «покрытие дат» (coverage) из кеша
        $coverage = Cache::tags([config('cache.tags')])->get($stockCacheKey, []);

        // 3. Если нужно проигнорировать кеш — обнуляем coverage
        if ($forceSkipCache) {
            $coverage = [];
        } else {
            // или нормализуем, если не делали этого раньше
            $coverage = CoverageHelper::normalizeCoverage($coverage);
        }

        // 4. Находим пропущенные участки в [startDate, endDate]
        $missingRanges = CoverageHelper::subtractCoverage($coverage, $startDate, $endDate);

        // 5. Если пропусков нет — данные уже загружены
        if (empty($missingRanges)) {
            return true;
        }

        foreach ($missingRanges as $range)
        {
            $rangeStart = Carbon::parse($range['start']);
            $rangeEnd   = Carbon::parse($range['end']);

            $iterationStart = $rangeStart->copy();
            while ($iterationStart->lte($rangeEnd)) {
                $iterationEnd = $iterationStart->copy()->addDays(99);
                if ($iterationEnd->gt($rangeEnd)) {
                    $iterationEnd = $rangeEnd->copy();
                }

                $data = $stock->requestDataFromApi($iterationStart, $iterationEnd);

                if ($data) {
                    // Сохраняем данные
                    foreach ($data as $datum)
                    {
                       $stock->saveDataFromApi($datum);
                    }

                    // Добавляем покрытие этих дат
                    $coverage = CoverageHelper::addCoverage($coverage, $iterationStart, $iterationEnd);
                } else {
                    // Логируем, что данных нет
                    LoggerHelper::getLogger()->info(sprintf(
                        'No any history for %s between %s and %s',
                        $stock->getSymbol(),
                        $iterationStart->format('Y-m-d'),
                        $iterationEnd->format('Y-m-d')
                    ));

                    // Можно решить, считать ли такой "пустой" интервал покрытым или нет.
                    // Допустим, считаем покрытым, чтобы в будущем не повторять запросы.
                    $coverage = CoverageHelper::addCoverage($coverage, $iterationStart, $iterationEnd);
                }

                // Переходим на следующий под-интервал
                $iterationStart = $iterationEnd->copy()->addDay();
            }
        }

        Cache::tags([config('cache.tags')])->put($stockCacheKey, $coverage);

        return true;
    }

}
