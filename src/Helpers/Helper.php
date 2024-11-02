<?php

namespace Common\Helpers;

use Illuminate\Contracts\Container\BindingResolutionException;

class Helper
{
    /**
     * Преобразует объект или массив в массив.
     *
     * Этот метод рекурсивно преобразует все вложенные объекты и массивы в массивы.
     *
     * @param object|array $obj Объект или массив для преобразования.
     *
     * @return array|object Преобразованный массив или объект, если входной параметр не является объектом или массивом.
     */
    public static function object_to_array($obj)
    {
        if (is_object($obj) || is_array($obj)) {
            $ret = (array)$obj;
            foreach ($ret as &$item) {
                // Рекурсивно обрабатываем каждый элемент, независимо от его типа.
                $item = self::object_to_array($item);
            }
            return $ret;
        }

        return $obj;
    }

    /**
     * Получите путь к общей папке.
     *
     * Используется для получения полного пути к папке public.
     *
     * @param string $path Дополнительный путь для добавления.
     *
     * @return string Полный путь к папке public.
     * @throws BindingResolutionException Если контейнер не может создать путь.
     */
    public static function public_path(string $path = ''): string
    {
        return app()->make('path.public') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }

    /**
     * Получите путь к папке ресурсов.
     *
     * @param string $path Дополнительный путь для добавления.
     *
     * @return string Полный путь к папке ресурсов.
     */
    public static function resource_path(string $path = ''): string
    {
        return app()->resourcePath($path);
    }

    /**
     * Получите путь к папке хранилища.
     *
     * @param string $path Дополнительный путь для добавления.
     *
     * @return string Полный путь к папке хранилища.
     */
    public static function storage_path(string $path = ''): string
    {
        return app('path.storage') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Получите путь к папке приложения.
     *
     * @param string $path Дополнительный путь для добавления.
     *
     * @return string Полный путь к папке приложения.
     */
    public static function app_path(string $path = ''): string
    {
        return app()->path($path);
    }

    /**
     * Получите путь к базе установки.
     *
     * @param string $path Дополнительный путь для добавления.
     *
     * @return string Полный путь к базе установки.
     */
    public static function base_path(string $path = ''): string
    {
        return app()->basePath($path);
    }

    /**
     * Получите путь конфигурации.
     *
     * @param string $path Дополнительный путь для добавления.
     *
     * @return string Полный путь к файлам конфигурации.
     */
    public static function config_path(string $path = ''): string
    {
        return app()->configPath($path);
    }

    /**
     * Получите путь к базе данных.
     *
     * @param string $path Дополнительный путь для добавления.
     *
     * @return string Полный путь к папке базы данных.
     */
    public static function database_path(string $path = ''): string
    {
        return app()->databasePath($path);
    }

    /**
     * Форматирует номер телефона в формат +7(XXX)XXX-XX-XX.
     *
     * @param string $number Номер телефона для форматирования.
     *
     * @return array|string|string[]|null Отформатированный номер телефона или null, если форматирование не удалось.
     */
    public static function plus_number_phone(string $number)
    {
        $number = preg_replace('/\D/', '', $number);
        return preg_replace('/(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/', '+7($2)$3-$4-$5', $number);
    }

    /**
     * Удаляет все символы, кроме цифр, из строки номера телефона.
     *
     * @param string $number Номер телефона для обработки.
     *
     * @return array|string|string[]|null Номер телефона, содержащий только цифры.
     */
    public static function only_numbers_phone(string $number)
    {
        return preg_replace('/\D/', '', $number);
    }

    /**
     * Сравнивает два массива на идентичность.
     *
     * Проверяет, идентичны ли структуры и значения двух массивов (включая вложенные элементы).
     *
     * @param mixed $array1 Первый массив или строка JSON для сравнения.
     * @param mixed $array2 Второй массив или строка JSON для сравнения.
     *
     * @return bool true, если массивы идентичны, иначе false.
     */
    public static function arraysAreEqual($array1, $array2): bool
    {
        // Преобразуем JSON-строки в массивы.
        $array1 = is_string($array1) ? self::object_to_array(json_decode($array1, true)) : $array1;
        if (is_array($array1)) {
            $array1 = self::object_to_array($array1);
        }

        $array2 = is_string($array2) ? self::object_to_array(json_decode($array2, true)) : $array2;
        if (is_array($array2)) {
            $array2 = self::object_to_array($array2);
        }

        // Проверяем, существование обоих массивов и являются ли массивы одинаковой длины.
        if ((!$array1 && $array2) || ($array1 && !$array2) || (count($array1) !== count($array2))) {
            return false;
        }

        // Рекурсивно сравниваем элементы массивов.
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!self::arraysAreEqual(json_encode($value), json_encode($array2[$key]))) {
                    return false;
                }
            } elseif ($value !== $array2[$key]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Преобразует ключи массива из camelCase в snake_case.
     *
     * @param array $array Массив для преобразования ключей.
     *
     * @return array Массив с ключами, преобразованными в snake_case.
     */
    public static function transformKeysToSnakeCase(array $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $snakeKey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));
            $result[$snakeKey] = $value;
        }
        return $result;
    }
}
