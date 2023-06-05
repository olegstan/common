<?php

namespace Common\Models\Catalog;

use Common\Models\BaseModel;

/**
 * Class BaseStock
 */
class BaseStock extends BaseModel
{
    protected $connection = 'catalog';

    /**
     * @param $term
     * @return array
     */
    public static function fullTextWildcards($term)
    {
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~',  '*'];
        $term = str_replace($reservedSymbols, ' ', $term);
        $term = preg_replace("/\s+/", ' ', $term);


        $words = explode(' ', $term);
        $expectedWords = [];

        foreach ($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            if (strlen($word) >= 2)
            {
                $expectedWords[] = '*' . $word . '*';
            }
        }

        return $expectedWords;
    }
}
