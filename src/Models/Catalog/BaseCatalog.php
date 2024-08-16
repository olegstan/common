<?php

namespace Common\Models\Catalog;

use Cache;
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
     * @param $stock
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function cacheHistory($stock, $startDate, $endDate): array
    {
        $key = $stock->getMorphClass() . '.' . $stock->id . '.' . $startDate->format('Y-m-d') . '/' . $endDate->format('Y-m-d');

        if (Cache::tags([config('cache.tags')])->has($key)) {
            return [true, Cache::tags([config('cache.tags')])->get($key), $key];
        }

        return [false, [], $key];
    }

    /**
     * @param $term
     * @return array
     */
    public static function fullTextWildcards($term): array
    {
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~',  '*', '"'];
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
