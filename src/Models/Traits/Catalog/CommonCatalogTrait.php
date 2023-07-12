<?php

namespace Common\Models\Traits\Catalog;

use Common\Models\Catalog\BaseCatalog;
use Common\Models\Currency;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait CommonCatalogTrait
{
    /**
     * @return array|void
     */
    public function getCurrency()
    {
        if (!empty($this->getCodeCurrency())) {
            if (is_array(json_decode($this->getCodeCurrency()))) {
                $curIds = [];

                foreach (json_decode($this->getCodeCurrency()) as $code) {
                    if ($code === 'SUR') {
                        $code = 'RUB';
                    }

                    $currency = Currency::getByCode($code);

                    if ($currency) {
                        $curIds[] = $currency->id;
                    }
                }

                return $curIds;
            }

            $currency = Currency::getByCode($this->getCodeCurrency());

            if ($currency) {
                return $currency->id;
            }
        }
    }

    /**
     * @param $original
     * @param $text
     * @param $translitText
     * @param $query
     * @param $prompt
     * @return mixed
     */
    public static function promptScopeSearch($original, $text, $translitText, $query, $prompt)
    {
        $originalWords = BaseCatalog::fullTextWildcards($original);
        $splitedWords = BaseCatalog::fullTextWildcards($text);
        $splitedTranslitWords = BaseCatalog::fullTextWildcards($translitText);

        return $query->where(function ($query) use ($originalWords, $prompt) {
            if ($originalWords) {
                foreach ($originalWords as $word) {
                    $query->whereRaw($prompt, $word);
                }
            }
        })
            ->orWhere(function ($query) use ($splitedWords, $prompt) {
                if ($splitedWords) {
                    foreach ($splitedWords as $word) {
                        $query->whereRaw($prompt, $word);
                    }
                }
            })
            ->orWhere(function ($query) use ($splitedTranslitWords, $prompt) {
                if ($splitedTranslitWords) {
                    foreach ($splitedTranslitWords as $word) {
                        $query->whereRaw($prompt, $word);
                    }
                }
            });
    }

    /**
     * @param $class
     * @return MorphOne
     */
    public function active($class): MorphOne
    {
        return $this->morphOne($class, 'item');
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icons
            ?
            config('microservices.catalog') . '/images/icons/' . $this->icons . '.svg'
            :
            config('microservices.catalog') . '/images/icons/default.svg';
    }
}