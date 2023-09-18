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
        $codeCur = $this->getCodeCurrency();

        if (!empty($codeCur)) {
            if (is_array($codeCur = json_decode($codeCur))) {
                $curIds = [];

                foreach ($codeCur as $code) {
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

            $currency = Currency::getByCode($codeCur);

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
     * @param $likePrompt
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