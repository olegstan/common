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
        self::promptScopeSearch($original, $text, $translitText, $query, $prompt);
    }
}