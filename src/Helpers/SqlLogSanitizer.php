<?php
namespace Common\Helpers;

class SqlLogSanitizer
{
    /**
     * Список чувствительных столбцов (в нижнем регистре).
     *
     * @var array
     */
    protected static $sensitiveColumns = ['api_token', 'token', 'password'];

    /**
     * Основной метод фильтрации SQL-запроса.
     *
     * @param string $sql Исходный SQL-запрос.
     * @return string Фильтрованный SQL-запрос.
     */
    public static function sanitize(string $sql): string
    {
        // Если строка пуста или не содержит чувствительных ключевых слов,
        // можно сразу вернуть исходный запрос
        if (empty($sql)) {
            return $sql;
        }

        // Если запрос INSERT, обрабатываем его отдельно
        if (stripos($sql, 'insert into') !== false) {
            $sql = self::sanitizeInsertQuery($sql);
        }
        // Если запрос UPDATE, обрабатываем отдельно
        elseif (stripos($sql, 'update') !== false && stripos($sql, ' set ') !== false) {
            $sql = self::sanitizeUpdateQuery($sql);
        }
        // В остальных случаях выполняем универсальную замену
        $sql = self::sanitizeGeneric($sql);

        return $sql;
    }

    /**
     * Фильтрует запрос INSERT.
     *
     * Ищет список столбцов и соответствующий список значений, после чего
     * для чувствительных столбцов заменяет значение на маскированное.
     *
     * @param string $sql Исходный INSERT-запрос.
     * @return string Фильтрованный запрос.
     */
    protected static function sanitizeInsertQuery(string $sql): string
    {
        // Регулярное выражение для поиска частей запроса:
        // 1) начало с открывающей скобкой столбцов,
        // 2) список столбцов,
        // 3) промежуточная часть с закрывающей скобкой и "values(",
        // 4) список значений,
        // 5) завершающая скобка.
        $pattern = '/(insert\s+into\s+.*?\()([^)]+)(\)\s+values\s*\()(.+?)(\))/is';

        return preg_replace_callback($pattern, function ($matches) {
            $columnsStr = $matches[2];
            $valuesStr  = $matches[4];

            // Разбиваем список столбцов по запятой и нормализуем их (без пробелов и кавычек)
            $columns = array_map(function ($col) {
                return strtolower(trim($col, " `\t\n\r\0\x0B"));
            }, explode(',', $columnsStr));

            // Разбиваем список значений с учётом запятых внутри строк
            $values = self::splitSqlValues($valuesStr);

            // Если число столбцов и значений не совпадает, оставляем запрос без изменений
            if (count($columns) !== count($values)) {
                return $matches[0];
            }

            // Для каждого чувствительного столбца маскируем соответствующее значение
            foreach ($columns as $i => $col) {
                if (in_array($col, self::$sensitiveColumns, true)) {
                    $values[$i] = self::maskSqlValue($values[$i]);
                }
            }

            $newValuesStr = implode(', ', $values);

            // Собираем обратно запрос
            return $matches[1] . $matches[2] . $matches[3] . $newValuesStr . $matches[5];
        }, $sql);
    }

    /**
     * Фильтрует запрос UPDATE.
     *
     * Ищет часть после ключевого слова SET до WHERE (если есть) и обрабатывает
     * каждое присвоение вида «<поле> = <значение>».
     *
     * @param string $sql Исходный UPDATE-запрос.
     * @return string Фильтрованный запрос.
     */
    protected static function sanitizeUpdateQuery(string $sql): string
    {
        // Регулярное выражение выделяет:
        //   1) часть от начала до ключевого слова SET,
        //   2) список присвоений,
        //   3) оставшуюся часть (начиная с WHERE или до конца строки).
        $pattern = '/(\bupdate\b.*?\bset\b)(.+?)(\bwhere\b.*|$)/is';

        return preg_replace_callback($pattern, function ($matches) {
            $prefix      = $matches[1];
            $assignments = $matches[2];
            $suffix      = $matches[3];

            // Разбиваем список присвоений по запятым с учётом кавычек
            $assignmentsArray = self::splitSqlValues($assignments);

            foreach ($assignmentsArray as &$assignment) {
                $assignment = trim($assignment);
                // Ищем присвоения для чувствительных столбцов.
                // Позволяем присутствие квалификатора (например, `table`.`column`).
                $assignment = preg_replace_callback(
                    '/((?:`[^`]+`\.)*`?(api_token|token|password)`?)\s*=\s*([\'"])([^\'"]+)\3/i',
                    function ($m) {
                        // $m[1]: полный идентификатор столбца (с квалификаторами, если есть)
                        // $m[2]: само имя столбца
                        // $m[3]: тип кавычек
                        // $m[4]: значение
                        $masked = self::maskToken($m[4]);
                        return $m[1] . ' = ' . $m[3] . $masked . $m[3];
                    },
                    $assignment
                );
            }
            $newAssignments = implode(', ', $assignmentsArray);

            return $prefix . $newAssignments . $suffix;
        }, $sql);
    }

    /**
     * Универсальная фильтрация запроса.
     *
     * Выполняется поиск конструкций вида:
     * <code>
     * [<таблица>.]<поле> = 'значение'
     * </code>
     * где <поле> — одно из чувствительных слов.
     *
     * @param string $sql Исходный запрос.
     * @return string Фильтрованный запрос.
     */
    protected static function sanitizeGeneric(string $sql): string
    {
        return preg_replace_callback(
            '/((?:`[^`]+`\.)*`?(api_token|token|password)`?)\s*=\s*([\'"])([^\'"]+)\3/i',
            function ($matches) {
                $masked = self::maskToken($matches[4]);
                return $matches[1] . ' = ' . $matches[3] . $masked . $matches[3];
            },
            $sql
        );
    }

    /**
     * Маскирует строку-токен: оставляет первые 3 и последние 3 символа, между ними вставляет '***'.
     * Если длина строки меньше или равна 6, возвращает строку, полностью заменённую звёздочками.
     *
     * @param string $token Исходное значение.
     * @return string Маскированное значение.
     */
    protected static function maskToken(string $token): string
    {
        $length = strlen($token);
        if ($length <= 6) {
            return str_repeat('*', $length);
        }
        $start = substr($token, 0, 3);
        $end   = substr($token, -3);
        return $start . '***' . $end;
    }

    /**
     * Маскирует значение из списка в INSERT‑запросе.
     *
     * Если значение обрамлено кавычками, сохраняет их, а содержимое маскирует.
     *
     * @param string $value Исходное значение.
     * @return string Маскированное значение.
     */
    protected static function maskSqlValue(string $value): string
    {
        $value = trim($value);
        if (
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'") ||
            (substr($value, 0, 1) === '"' && substr($value, -1) === '"')
        ) {
            $quote = substr($value, 0, 1);
            $inner = substr($value, 1, -1);
            $masked = self::maskToken($inner);
            return $quote . $masked . $quote;
        }
        return $value;
    }

    /**
     * Разбивает строку SQL‑элементов (например, список значений или присвоений)
     * по запятым, игнорируя запятые, находящиеся внутри одинарных или двойных кавычек.
     *
     * @param string $string Строка для разбивки.
     * @return array Массив отдельных элементов.
     */
    protected static function splitSqlValues(string $string): array
    {
        $pattern = '/,(?=(?:[^\'"]*[\'"][^\'"]*[\'"])*[^\'"]*$)/';
        return preg_split($pattern, $string);
    }
}
