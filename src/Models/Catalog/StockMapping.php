<?php

namespace Common\Models\Catalog;

use Common\Models\Catalog\Cbond\CbondStock;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeStock;
use Common\Models\Catalog\Tinkoff\TinkoffStock;
use Common\Models\Catalog\TradingView\TradingViewTicker;
use Common\Models\Catalog\Yahoo\YahooStock;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $ticker
 * @property integer $moscow_exchange_stocks_id
 * @property integer $cbond_stocks_id
 * @property integer $tinkoff_stocks_id
 * @property integer $yahoo_stocks_id
 * @property integer $tv_tickers_id
 */
class StockMapping extends BaseCatalog
{
    use HasFactory;

    /**
     * Соответствие моделей таблицам в stock_mappings.
     */
    public const TABLE_COLUMNS = [
        MoscowExchangeStock::class => 'moscow_exchange_stocks_id',
        CbondStock::class => 'cbond_stocks_id',
        TinkoffStock::class => 'tinkoff_stocks_id',
        YahooStock::class => 'yahoo_stocks_id',
        TradingViewTicker::class => 'tv_tickers_id',
    ];
    /**
     * Соответствие столбцов `stock_mappings` моделям и их полям.
     */
    public const TABLE_NAMES = [
        MoscowExchangeStock::class => ['fields' => ['secid', 'isin']],
        CbondStock::class => ['fields' => ['secid', 'isin']],
        TinkoffStock::class => ['fields' => ['figi', 'ticker', 'isin']],
        YahooStock::class => ['fields' => ['symbol']],
        TradingViewTicker::class => ['fields' => ['symbol']],
    ];
    /**
     * @var string
     */
    public $table = 'stock_mappings';
    /**
     * @var array
     */
    protected $fillable = [
        'ticker',
        'moscow_exchange_stocks_id',
        'cbond_stocks_id',
        'tinkoff_stocks_id',
        'yahoo_stocks_id',
        'tv_tickers_id',
    ];
    /**
     * @var array
     */
    protected $casts = [
        'ticker' => 'string',
        'moscow_exchange_stocks_id' => 'integer',
        'cbond_stocks_id' => 'integer',
        'custom_stocks_id' => 'integer',
        'yahoo_stocks_id' => 'integer',
        'tv_tickers_id' => 'integer',
    ];

    /**
     * Возвращает модели других каталогов, по переданному каталогу (переданный каталог не будет включен в возвращаемое
     * значение)
     *
     * @param MoscowExchangeStock|CbondStock|YahooStock|TradingViewTicker $catalog
     *
     * @return array
     */
    public static function getSimilar(BaseCatalog $catalog): array
    {
        $mapping = self::getMapping($catalog);

        if (!$mapping) {
            return [];
        }

        //TODO:: Как добавим тинек в каталог для активов, надо его сюда будет внести
        $result = [
            'moscow_exchange_stocks' => $mapping->moscowExchangeStock,
            'cbond_stocks' => $mapping->cbondStock,
            'yahoo_stocks' => $mapping->yahooStock,
            'tv_tickers' => $mapping->tvTicker,
        ];

        // Исключаем переданную модель из массива
        return array_filter($result, static function ($item) use ($catalog) {
            return $item && $item->id !== $catalog->id;
        });
    }

    /**
     * Возвращает запись модели по переданному каталогу
     *
     * @param BaseCatalog $catalog
     *
     * @return ?StockMapping
     */
    private static function getMapping(BaseCatalog $catalog): ?StockMapping
    {
        $firstSymbol = $catalog->getSymbol();
        $secondSymbol = $catalog->getSecondSymbol();

        return self::with([
            'moscowExchangeStock',
            'cbondStock',
            'yahooStock',
            'tvTicker',
        ])->where(static function ($query) use ($firstSymbol, $secondSymbol) {
            $query->where('ticker', $firstSymbol)
                ->orWhere('ticker', $secondSymbol);
        })->first();
    }

    /**
     * Возвращает айдишники других каталогов, по переданному каталогу (переданный каталог не будет включен в
     * возвращаемое значение)
     *
     * @param MoscowExchangeStock|CbondStock|YahooStock|TradingViewTicker $catalog
     *
     * @return array
     */
    public static function getSimilarIds(BaseCatalog $catalog): array
    {
        $mapping = self::getMapping($catalog);

        if (!$mapping) {
            return [];
        }

        //TODO:: Как добавим тинек в каталог для активов, надо его сюда будет внести
        $result = [
            'moscow_exchange_stocks_id' => $mapping->moscow_exchange_stocks_id,
            'cbond_stocks_id' => $mapping->cbond_stocks_id,
            'yahoo_stocks_id' => $mapping->yahoo_stocks_id,
            'tv_tickers_id' => $mapping->tv_tickers_id,
        ];

        // Убираем айдишник переданной модели из массива
        return array_filter($result, static fn($id) => $id !== $catalog->id);
    }

    /**
     * Обновляет существующую запись, если появилась новая связь.
     *
     * @param StockMapping $mapping
     * @param MoscowExchangeStock|CbondStock|TinkoffStock|TradingViewTicker|YahooStock $model
     *
     * @return void
     */
    public static function updateExistingMapping(StockMapping $mapping, $model): void
    {
        $modelClass = get_class($model);
        $newId = $model->id;

        if (isset(self::TABLE_COLUMNS[$modelClass])) {
            $column = self::TABLE_COLUMNS[$modelClass];

            if (!$mapping->$column) {
                $mapping->$column = $newId;
                $mapping->save();
            }
        }
    }

    /**
     * Создает новую запись в `stock_mappings`, если найдено больше 1 связи.
     *
     * @param string $ticker
     *
     * @return void
     */
    public static function createNewMapping(string $ticker): void
    {
        $mapping = new StockMapping();
        $mapping->ticker = $ticker;

        $relatedCount = 0;

        /**
         * @var $modelClass MoscowExchangeStock|YahooStock|TinkoffStock|CbondStock|TradingViewTicker
         */
        foreach (self::TABLE_NAMES as $modelClass => $config) {
            $query = $modelClass::query();

            foreach ($config['fields'] as $field) {
                $query->orWhere($field, $ticker);
            }

            $id = $query->value('id');

            if ($id) {
                $column = self::TABLE_COLUMNS[$modelClass];
                $mapping->$column = $id;
                $relatedCount++;
            }
        }

        // Записываем в таблицу только если найдено больше 1 связи
        if ($relatedCount > 1) {
            $mapping->save();
        }
    }

    /**
     * @return BelongsTo
     */
    public function moscowExchangeStock(): BelongsTo
    {
        return $this->belongsTo(MoscowExchangeStock::class, 'moscow_exchange_stocks_id');
    }

    /**
     * @return BelongsTo
     */
    public function cbondStock(): BelongsTo
    {
        return $this->belongsTo(CbondStock::class, 'cbond_stocks_id');
    }

    /**
     * @return BelongsTo
     */
    public function tinkoffStock(): BelongsTo
    {
        return $this->belongsTo(TinkoffStock::class, 'tinkoff_stocks_id');
    }

    /**
     * @return BelongsTo
     */
    public function yahooStock(): BelongsTo
    {
        return $this->belongsTo(YahooStock::class, 'yahoo_stocks_id');
    }

    /**
     * @return BelongsTo
     */
    public function tvTicker(): BelongsTo
    {
        return $this->belongsTo(TradingViewTicker::class, 'tv_tickers_id');
    }
}
