<?php

namespace Common\Models\Traits\Catalog;

use Common\Models\Catalog\BaseCatalog;
use Common\Models\Currency;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait CommonCatalogTrait
{
    /**
     * @return int|false
     */
    public function getCurrency()
    {
        $codeCur = $this->getCodeCurrency();

        if (empty($codeCur)) {
            return null;
        }

        if (!is_array(json_decode($codeCur))) {
            $currency = Currency::getByCode($codeCur);
            return $currency->id ?? null;
        }

        //Массивы валют у Мосбиржи. Что бы не ломать логику, будем возвращать рубль (если есть) или первую найденную валюту
        //Первую найденную, тк к примеру в SearchActive для контроллера мы должны будем один актив продублировать на количество валют
        $codeCur = json_decode($codeCur);

        if (in_array('SUR', $codeCur) || in_array('RUB', $codeCur)) {
            $currency = Currency::getByCode('RUB');
            return $currency->id ?? null;
        }

        foreach ($codeCur as $code) {
            $currency = Currency::getByCode($code);

            if ($currency) {
                return $currency->id;
            }
        }

        return null;
    }

    /**
     * @param $original
     * @param $text
     * @param $translitText
     * @param $query
     * @param $prompt
     * @param $likePrompt
     *
     * @return mixed
     */
    public static function promptScopeSearch($original, $text, $translitText, $query, $prompt, $likePrompt)
    {
        $originalWords = BaseCatalog::fullTextWildcards($original);
        $splitedWords = BaseCatalog::fullTextWildcards($text);
        $splitedTranslitWords = BaseCatalog::fullTextWildcards($translitText);

        return $query->where(function ($query) use ($originalWords, $prompt, $likePrompt) {
            if ($originalWords) {
                foreach ($originalWords as $word) {
                    $query->whereRaw($prompt, $word)
                        ->orWhere(function ($queryRaw) use ($likePrompt, $word, $originalWords) {
                            if (count($originalWords) === 1) {
                                foreach ($likePrompt as $item) {
                                    $queryRaw->orWhereRaw($item, str_replace('*', '%', $word));
                                }
                            }
                        });
                }
            }
        })
            ->orWhere(function ($query) use ($splitedWords, $prompt, $likePrompt) {
                if ($splitedWords) {
                    foreach ($splitedWords as $word) {
                        $query->whereRaw($prompt, $word)
                            ->orWhere(function ($queryRaw) use ($likePrompt, $word, $splitedWords) {
                                if (count($splitedWords) === 1) {
                                    foreach ($likePrompt as $item) {
                                        $queryRaw->orWhereRaw($item, str_replace('*', '%', $word));
                                    }
                                }
                            });
                    }
                }
            })
            ->orWhere(function ($query) use ($splitedTranslitWords, $prompt, $likePrompt) {
                if ($splitedTranslitWords) {
                    foreach ($splitedTranslitWords as $word) {
                        $query->whereRaw($prompt, $word)
                            ->orWhere(function ($queryRaw) use ($likePrompt, $word, $splitedTranslitWords) {
                                if (count($splitedTranslitWords) === 1) {
                                    foreach ($likePrompt as $item) {
                                        $queryRaw->orWhereRaw($item, str_replace('*', '%', $word));
                                    }
                                }
                            });
                    }
                }
            });
    }

    /**
     * @param $class
     *
     * @return MorphOne
     */
    public function active(): MorphOne
    {
        return $this->morphOne(config('common.active'), 'item');
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