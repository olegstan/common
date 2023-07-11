<?php

namespace Common\Models\Catalog;

use Common\Models\Base;
use Common\Models\BaseModel;

/**
 * Class BaseStock
 */
class BaseCatalog extends BaseModel
{
    protected $connection = 'catalog';

    public function __construct(array $attributes = [])
    {
        $this->table = config('database.connections.catalog.database') . '.' . $this->table;

        //так написано, чтобы сработал конструктор, но не родительский а нативный Laravel,
        //иначе получается некорректное название таблицы, поскольку оно прибавляется два раза
        //back.fincatalog.currencies, должно быть fincatalog.currencies
        $reflectionMethod = new \ReflectionMethod(get_parent_class(get_parent_class(get_parent_class($this))), '__construct');
        $reflectionMethod->invoke($this, $attributes);
    }

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
