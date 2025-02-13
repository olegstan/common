<?php

namespace Common\Helpers;

use Carbon\Carbon;

class CoverageHelper
{
    /**
     * Сливает/мерджит пересекающиеся интервалы в массиве coverage, а затем сортирует.
     * Возвращает список непересекающихся интервалов по возрастанию.
     *
     * Формат coverage: [
     *   [ 'start' => 'YYYY-mm-dd', 'end' => 'YYYY-mm-dd' ],
     *   ...
     * ]
     *
     * @param  array $coverage
     * @return array
     */
    public static function normalizeCoverage(array $coverage): array
    {
        if (empty($coverage)) {
            return [];
        }

        // Шаг 1. Переводим все start/end в Carbon и сортируем по start
        $normalized = [];
        foreach ($coverage as $interval) {
            $normalized[] = [
                'start' => Carbon::parse($interval['start']),
                'end'   => Carbon::parse($interval['end']),
            ];
        }
        usort($normalized, function ($a, $b) {
            return $a['start']->lt($b['start']) ? -1 : 1;
        });

        // Шаг 2. Сливаем пересекающиеся интервалы
        $merged = [];
        $current = $normalized[0];

        for ($i = 1; $i < count($normalized); $i++) {
            $next = $normalized[$i];

            // Если пересекается с текущим интервалом
            if ($next['start']->lte($current['end']->copy()->addDay())) {
                // Расширяем end, если нужно
                if ($next['end']->gt($current['end'])) {
                    $current['end'] = $next['end'];
                }
            } else {
                // Если не пересекается, добавляем текущий и переходим к next
                $merged[] = $current;
                $current = $next;
            }
        }
        // Добавляем последний накопленный current
        $merged[] = $current;

        // Шаг 3. Переводим обратно в строки
        $result = [];
        foreach ($merged as $interval) {
            $result[] = [
                'start' => $interval['start']->format('Y-m-d'),
                'end'   => $interval['end']->format('Y-m-d'),
            ];
        }

        return $result;
    }

    /**
     * Добавление нового интервала [newStart, newEnd] в массив coverage.
     * После добавления — объединяем пересекающиеся сегменты.
     *
     * @param  array   $coverage   [["start" => "...", "end" => "..."], ...]
     * @param  Carbon  $newStart
     * @param  Carbon  $newEnd
     * @return array
     */
    public static function addCoverage(array $coverage, Carbon $newStart, Carbon $newEnd): array
    {
        // Добавляем новый интервал
        $coverage[] = [
            'start' => $newStart->format('Y-m-d'),
            'end'   => $newEnd->format('Y-m-d'),
        ];

        // Упорядочиваем и сливаем пересечения
        return self::normalizeCoverage($coverage);
    }

    /**
     * Вычисляет "пропущенные" промежутки в заданном интервале [startDate, endDate],
     * исходя из уже имеющегося coverage.
     *
     * Возвращает массив вида:
     * [
     *   ["start" => "YYYY-mm-dd", "end" => "YYYY-mm-dd"],
     *   ...
     * ]
     *
     * @param  array   $coverage    // [["start" => "...", "end" => "..."], ...]
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return array
     */
    public static function subtractCoverage(array $coverage, Carbon $startDate, Carbon $endDate): array
    {
        // Если покрытие пустое, весь диапазон [startDate, endDate] — пропущен
        if (empty($coverage)) {
            return [[
                'start' => $startDate->format('Y-m-d'),
                'end'   => $endDate->format('Y-m-d'),
            ]];
        }

        $requestStart = $startDate->format('Y-m-d');
        $requestEnd   = $endDate->format('Y-m-d');

        // Идём по интервалам coverage и вычитаем их из [requestStart, requestEnd]
        $missing = [];
        $currentStart = $requestStart;

        foreach ($coverage as $interval) {
            $covStart = $interval['start'];
            $covEnd   = $interval['end'];

            // Если этот интервал покрытия заканчивается до того,
            // как начинается currentStart, пропускаем
            if ($covEnd < $currentStart) {
                continue;
            }

            // Если начинается после requestEnd, значит дальше все дни — пропущены
            if ($covStart > $requestEnd) {
                break;
            }

            // Если есть "зазор" [currentStart, covStart - 1]
            if ($covStart > $currentStart) {
                $gapStart = $currentStart;
                // gapEnd — это день перед covStart (covStart - 1),
                // но также не должен выходить за границу requestEnd
                $gapEnd   = self::minDate(self::prevDay($covStart), $requestEnd);

                if ($gapStart <= $gapEnd) {
                    $missing[] = [
                        'start' => $gapStart,
                        'end'   => $gapEnd,
                    ];
                }
            }

            // Обновляем currentStart на день после покрытия
            if ($covEnd >= $currentStart) {
                $currentStart = self::nextDay($covEnd);
            }

            // Если вышли за пределы requestEnd
            if ($currentStart > $requestEnd) {
                break;
            }
        }

        // Если после всего coverage все ещё остался хвост [currentStart, requestEnd]
        if ($currentStart <= $requestEnd) {
            $missing[] = [
                'start' => $currentStart,
                'end'   => $requestEnd,
            ];
        }

        return $missing;
    }

    /**
     * Вспомогательная функция: получить предыдущий день (строка YYYY-mm-dd).
     */
    public static function prevDay(string $dateString): string
    {
        return Carbon::parse($dateString)->subDay()->format('Y-m-d');
    }

    /**
     * Вспомогательная функция: получить следующий день (строка YYYY-mm-dd).
     */
    public static function nextDay(string $dateString): string
    {
        return Carbon::parse($dateString)->addDay()->format('Y-m-d');
    }

    /**
     * Минимальная из двух строковых дат (формат YYYY-mm-dd).
     */
    public static function minDate(string $date1, string $date2): string
    {
        return (strtotime($date1) < strtotime($date2)) ? $date1 : $date2;
    }
}
