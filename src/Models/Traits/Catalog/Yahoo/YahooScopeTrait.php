<?php
namespace App\src\Models\Traits\Catalog\Yahoo;


trait YahooScopeTrait
{
    /**
     * @param $query
     * @param $original
     * @param $text
     * @param $translitText
     * @return void
     */
    public function scopeSearch($query, $original, $text, $translitText)
    {
        $prompt = 'MATCH (`yahoo_stocks`.`symbol`, `yahoo_stocks`.`name`) AGAINST (? IN BOOLEAN MODE)';
        self::promptScopeSearch($original, $text, $translitText, $query, $prompt);
    }
}