<?php
namespace Common\Models\Traits\Catalog\MoscowExchange;


trait MoexScopeTrait
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
        $prompt = 'MATCH (`moscow_exchange_stocks`.`secid`,`moscow_exchange_stocks`.`name`,`moscow_exchange_stocks`.`isin`,`moscow_exchange_stocks`.`latname`,`moscow_exchange_stocks`.`shortname`) AGAINST (? IN BOOLEAN MODE)';
        self::promptScopeSearch($original, $text, $translitText, $query, $prompt);
    }
}