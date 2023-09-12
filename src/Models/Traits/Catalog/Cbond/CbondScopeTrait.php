<?php
namespace Common\Models\Traits\Catalog\Cbond;


trait CbondScopeTrait
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
        $prompt = 'MATCH (`cbond_stocks`.`name`,`cbond_stocks`.`isin`,`cbond_stocks`.`latname`,`cbond_stocks`.`shortname`) AGAINST (? IN BOOLEAN MODE)';
        $likePrompt = ['cbond_stocks.name LIKE ?','cbond_stocks.isin LIKE ?','cbond_stocks.latname LIKE ?','cbond_stocks.shortname LIKE ?',];
        self::promptScopeSearch($original, $text, $translitText, $query, $prompt, $likePrompt);
    }
}