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
     * @return string
     */
    public static function getCacheKey($stock, $startDate, $endDate)
    {
        return $stock->getMorphClass() . '.' . $stock->id . '.' . $startDate->format('Y-m-d') . '/' . $endDate->format('Y-m-d');
    }

    /**
     * @param $key
     * @return array
     */
    public static function cacheHistory($key): array
    {
        if (Cache::tags([config('cache.tags')])->has($key)) {
            return [true, Cache::tags([config('cache.tags')])->get($key)];
        }

        return [false, []];
    }

    /**
     * @param $term
     * @return array
     */
    public static function fullTextWildcards($term): array
    {
        $words = explode(' ', $term);
        $expectedWords = [];

        foreach ($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            if (strlen($word) >= 2) {
                $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~', '*', '"', '.'];
                $word = str_replace($reservedSymbols, ' ', $word);
                $word = preg_replace("/\s+/", ' ', $word);
                $expectedWords[] = '*' . $word . '*';
            }
        }

        return $expectedWords;
    }

    /**
     * @param $stock
     * @return string
     */
    protected function getStockCacheKey(): string
    {
        // Например: catalog.3.12345
        return $this->getMorphClass() . '.' . $this->id;
    }
}
