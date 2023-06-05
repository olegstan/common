<?php
namespace Common\Helpers;


use Transliterator\Settings;
use Transliterator\Transliterator;

class Translit
{
    /**
     * @param $string
     * @return array
     */
    public static function make($string): array
    {
        $transliteration = new Transliterator(Settings::LANG_RU);

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