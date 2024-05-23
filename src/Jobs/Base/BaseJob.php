<?php

namespace Common\Jobs\Base;

use Common\Jobs\Traits\ConstructBaseJobTrait;
use Common\Jobs\Traits\DestructBaseJobTrait;
use ReflectionClass;
use ReflectionException;
use Storage;

class BaseJob extends Job
{
    use ConstructBaseJobTrait;
    use DestructBaseJobTrait;

    /**
     * Хранится путь, название и значение статичных переменных файлов
     *
     * @var array
     */
    public array $staticValues = [];

    /**
     * Аналог переменной выше, только статичная
     *
     * @var array
     */
    public static array $allStaticValues = [];

    /**
     * Хранение для скрипта namespace файла для инициализации класса
     *
     * @var string
     */
    protected const NAMESPACE_MODELS = [
        'App' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR,
        'App' . DIRECTORY_SEPARATOR . 'Jobs' . DIRECTORY_SEPARATOR,
        'App' . DIRECTORY_SEPARATOR . 'Helpers' . DIRECTORY_SEPARATOR,
        'App' . DIRECTORY_SEPARATOR . 'Traits' . DIRECTORY_SEPARATOR,
        'Common' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR,
        'Common' . DIRECTORY_SEPARATOR . 'Jobs' . DIRECTORY_SEPARATOR,
        'Common' . DIRECTORY_SEPARATOR . 'Helpers' . DIRECTORY_SEPARATOR,
    ];

    /**
     *
     */
    public function __construct()
    {
        //Оставлю пока на всяк случай
        //Метод сохраняет все статические значения моделей
        //в массиве $this->staticValues
//        $this->recordStaticValues();

        //Если запустили rabbit, при первой итерации будет пусто, но в последующих
        //здесь уже будет все записано и незачем перезаписывать, тк дэфолтные значения не поменяются
        if (!empty(self::$allStaticValues)) {
            return;
        }

        $fileSysNames = ['app', 'common'];
        array_walk($fileSysNames, [$this, 'recordAllStaticValues']);
    }

    /**
     * Записывает все статические значения из всех файлов в указанной файловой системе.
     *
     * @param string $fileSysName Имя файловой системы для сканирования.
     *
     * @return void
     * @throws ReflectionException Если процесс отражения не удался.
     */
    public function recordAllStaticValues(string $fileSysName): void
    {
        //Пройдемся по всем файлам проекта
        foreach (Storage::disk($fileSysName)->allFiles() as $file) {
            //Для корректного namespace обработаем путь
            $file = ucfirst($fileSysName) . DIRECTORY_SEPARATOR . str_replace(['.php', '/'], ['', '\\'], $file);

            // Пропускать файлы, которые не находятся в пространстве имен App\helpers, не имеют метода getAllStaticValues,
            // или имеют родительский класс
            if ($file === 'App\helpers' ||
                !method_exists($file, 'getAllStaticValues') ||
                (new ReflectionClass($file))->getParentClass()) {
                continue;
            }

            $reflection = new ReflectionClass($file);

            //запишем все переменные из трейтов
            foreach ($reflection->getTraits() as $trait) {
                foreach ($trait->getStaticProperties() as $name => $value) {
                    self::$allStaticValues[$file][$name] = $value;
                }
            }

            //запишем все переменные из классов
            foreach ($reflection->getStaticProperties() as $name => $value) {
                self::$allStaticValues[$file][$name] = $value;
            }
        }
    }
}
