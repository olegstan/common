<?php

namespace Common\Jobs\Base;

use App\Traits\Job\ConstructBaseJobTrait;
use App\Traits\Job\DestructBaseJobTrait;
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
    ];

    /**
     * @throws ReflectionException
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

        $this->recordAllStaticValues();
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function recordAllStaticValues(): void
    {
        //Пройдемся по всем файлам проекта
        foreach (Storage::disk('app')->allFiles() as $file) {
            //Для корректного namespace обработаем путь
            $file = 'App' . DIRECTORY_SEPARATOR . str_replace(['.php', '/'], ['', '\\'], $file);

            //файлы в которых надо откатить статичные значения будут содержать указанный метод
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
