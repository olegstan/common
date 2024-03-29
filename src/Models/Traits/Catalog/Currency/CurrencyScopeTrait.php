<?php
namespace Common\Models\Traits\Catalog\Currency;


trait CurrencyScopeTrait
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
        $prompt = 'MATCH (char_code, name) AGAINST (? IN BOOLEAN MODE)';
        $likePrompt = ['name LIKE ?','char_code LIKE ?'];
        self::promptScopeSearch($original, $text, $translitText, $query, $prompt, $likePrompt);
    }
}