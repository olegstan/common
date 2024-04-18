<?php
namespace Common\Helpers;


use Behat\Transliterator\Transliterator;
use Transliterator\Settings;

class Translit extends Transliterator
{
    /**
     * Метод не относящийся к наследуемой библиотеке
     * метод пока останется для, что бы не возникали ошибки в уже существующих местах
     *
     * @param $string
     * @return array
     */
    public static function make($string): array
    {
        //Обязательно оставить путь, тк могут быть конфликты 2 библиотек
        $transliteration = new \Transliterator\Transliterator(Settings::LANG_RU);

        $text = $string;

        //исключение для правильного поиска так как транслит ищёт yandex, а не yandex
        if(strtolower($text) === 'яндекс')
        {
            $text = 'yandex';
        }else{
            $text = $transliteration->cyr2Lat($text);
        }
        $translitText = $transliteration->lat2Cyr($text);

        return [$string, $text, $translitText];
    }
}