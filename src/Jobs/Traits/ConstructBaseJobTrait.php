<?php

namespace Common\Jobs\Traits;

use ReflectionClass;
use ReflectionException;
use Storage;

trait ConstructBaseJobTrait
{
    /**
     * Запишите статические значения моделей.
     *
     * @return void
     */
    public function recordStaticValues(): void
    {
        $disks = ['models', 'jobs', 'helpers'];

        foreach ($disks as $disk) {
            $files = Storage::disk($disk)->allFiles();
            array_walk($files, [$this, 'createModelInstance']);
        }
    }

    /**
     * Создайте экземпляр модели и сохраните его статические значения.
     *
     * @param string $path Путь к файлу модели.
     *
     * @return void
     * @throws ReflectionException
     */
    private function createModelInstance(string $path): void
    {
        // Извлеките пространство имен из пути
        $className = $this->getClassFromPath($path);

        // Получите ReflectionClass для модели.
        $reflectionClass = new ReflectionClass($className);
        // Получить все статические свойства класса
        $staticProperties = $reflectionClass->getStaticProperties();
        // Перебрать каждое статическое свойство
        foreach ($staticProperties as $propertyName => $propertyValue) {
            // Проверьте, установлено ли свойство и его значение не равно нулю.
            if (isset($className::$$propertyName) &&
                $reflectionClass->getStaticPropertyValue($propertyName) !== null) {
                // Сохраните значение свойства в массив статических значений.
                $this->staticValues[$className][$propertyName] = $propertyValue;
            }
        }
    }

    /**
     * Получить пространство имен класса из указанного пути.
     *
     * @param string $path Путь для извлечения пространства имен класса.
     *
     * @return string Полное квалифицированное имя класса.
     */
    private function getClassFromPath(string $path): string
    {
        // Разделить путь по прямой или обратной косой черте
        $splits = preg_split("~/|\\\\~", $path);

        // Построить пространство имен класса, перебирая сегменты пути
        $class = '';
        foreach ($splits as $key => $explode) {
            // Добавить сегмент к пространству имен класса
            // Исключить первый сегмент, так как это корневое пространство имен
            // Для последнего сегмента удалить расширение файла
            $class .= ($key > 0 ? DIRECTORY_SEPARATOR : '') . ($key === count($splits) - 1
                    ? str_replace('.php', '', $explode)
                    : $explode);
        }

        // Получить полное квалифицированное имя класса, вызвав метод getClassFullPath
        return $this->getClassFullPath($class);
    }

    /**
     * Получите полный путь к классу.
     *
     * @param string $class Имя класса.
     *
     * @return string|null Полный путь к классу или значение NULL, если оно не найдено.
     */
    public function getClassFullPath(string $class): ?string
    {
        // Перебирайте модели пространства имен.
        foreach (self::NAMESPACE_MODELS as $namespace) {
            // Создайте полный путь к классу.
            $fullPath = $namespace . $class;

            // Проверьте, существует ли класс или черта.
            if (class_exists($fullPath) || trait_exists($fullPath)) {
                // Верните полный путь к классу.
                return $fullPath;
            }
        }

        // Возвращайте значение null, если класс или признак не найден.
        return null;
    }
}