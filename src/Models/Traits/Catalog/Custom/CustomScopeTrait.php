<?php
namespace Common\Models\Traits\Catalog\Custom;



trait CustomScopeTrait
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
        $prompt = 'MATCH (`custom_stocks`.`name`, `custom_stocks`.`symbol`) AGAINST (? IN BOOLEAN MODE)';
        $likePrompt = ['custom_stocks.name LIKE ?', 'custom_stocks.symbol LIKE ?'];
        self::promptScopeSearch($original, $text, $translitText, $query, $prompt, $likePrompt);
    }
}