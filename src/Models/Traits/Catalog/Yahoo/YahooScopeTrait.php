<?php
namespace Common\Models\Traits\Catalog\Yahoo;

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
        $likePrompt = ['yahoo_stocks.symbol LIKE ?','yahoo_stocks.name LIKE ?'];
        self::promptScopeSearch($original, $text, $translitText, $query, $prompt, $likePrompt);
    }
}