<?php

namespace Common\Helpers\Catalog;

use Carbon\Carbon;
use Common\Helpers\Helper;
use Common\Helpers\LoggerHelper;
use Common\Helpers\Translit;
use Common\Models\Catalog\BaseCatalog;
use Common\Models\Catalog\Cbond\CbondStock;
use Common\Models\Catalog\Currency\CbCurrency;
use Common\Models\Catalog\Custom\CustomStock;
use Common\Models\Catalog\MoscowExchange\MoscowExchangeStock;
use Common\Models\Catalog\Yahoo\YahooStock;
use Elasticsearch\ClientBuilder;
use Exception;
use stdClass;

class CatalogSearch
{
    // Поля для поиска с весовыми коэффициентами для 'moscow_exchange_stocks'
    protected const MOSCOW_STOCKS_FIELDS = ['secid', 'isin', 'name', 'shortname', 'latname'];

    // Поля для поиска по 'cb_currencies'
    protected const CB_CURRENCIES_FIELDS = ['name', 'cb_id', 'char_code'];

    // Поля для поиска по 'custom_stocks'
    protected const CUSTOM_STOCKS_FIELDS = ['name', 'symbol'];

    // Поля для поиска по 'yahoo_stocks'
    protected const YAHOO_STOCKS_FIELDS = ['name', 'symbol', 'sector', 'industry'];

    // Поля для поиска по 'cbond_stocks'
    protected const CBOND_STOCKS_FIELDS = ['name', 'symbol', 'isin', 'shortname', 'latname'];

    public const MOEX_INDEX = 'catalog.moscow_exchange_stocks';
    public const YAHOO_INDEX = 'catalog.yahoo_stocks';
    public const CBOND_INDEX = 'catalog.cbond_stocks';
    public const CB_INDEX = 'catalog.cb_currencies';
    public const CUSTOM_INDEX = 'catalog.custom_stocks';

    public const ALL_INDEXES = [
        self::MOEX_INDEX,
        self::YAHOO_INDEX,
        self::CBOND_INDEX,
        self::CB_INDEX,
        self::CUSTOM_INDEX,
    ];

    /**
     * Клиент Elasticsearch
     */
    protected $client;

    /**
     * Конструктор: инициализирует клиент Elasticsearch
     */
    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts(config('elasticsearch.config.hosts'))
            ->build();
    }

    /**
     * Проверка состояния Elasticsearch
     *
     * @param ClientBuilder|null $client
     *
     * @return bool
     */
    public static function isElasticsearchHealthy(?ClientBuilder $client = null): bool
    {
        try {
            if (!$client) {
                // Инициализация Elasticsearch клиента
                $client = ClientBuilder::create()
                    ->setHosts(config('elasticsearch.config.hosts'))
                    ->build();
            }

            // Выполняем запрос на проверку состояния Elasticsearch
            $health = $client->cluster()->health();

            // Проверяем статус кластера (green или yellow считаются нормальными)
            if (in_array($health['status'], ['green', 'yellow'])) {
                return true;
            }

            LoggerHelper::getLogger()->error('Отсутствует подключение к эластику', [
                'status' => $health['status'],
                'health' => $health,
            ]);

            return false;
        } catch (Exception $e) {
            // Логируем ошибку и возвращаем false, если не удалось подключиться к Elasticsearch
            LoggerHelper::getLogger()->error($e->getMessage());
            return false;
        }
    }

    /**
     * Индексация записи в Elasticsearch
     *
     * @param $record
     * @param string $indexName
     */
    public static function indexRecordInElasticsearch($record, string $indexName): void
    {
        $self = new self();

        if ($record instanceof BaseCatalog) {
            $record = $record->toArray();
        } elseif (is_object($record)) {
            $record = Helper::object_to_array($record);

            if (isset($record['created_at'], $record['updated_at'])) {
                $record['created_at'] = Carbon::createFromFormat('Y-m-d H:i:s', $record['created_at']);
                $record['updated_at'] = Carbon::createFromFormat('Y-m-d H:i:s', $record['updated_at']);
            }
        }

        try {
            $index = "catalog.$indexName"; // Указываем имя индекса

            // Проверяем, существует ли запись в Elasticsearch
            $exists = $self->client->exists([
                'index' => $index,
                'id' => $record['id'],
            ]);

            if ($exists) {
                return;
            }

            // Параметры для индексации
            $params = [
                'index' => $index,  // Имя индекса
                'id' => $record['id'],  // ID записи
                'body' => $record,  // Преобразуем запись в массив
            ];

            // Индексация записи
            $self->client->index($params);
        } catch (Exception $e) {
            // Логируем ошибку при индексации
            LoggerHelper::getLogger(class_basename($self))
                ->error(
                    "Ошибка индексации записи с ID {$record['id']} в Elasticsearch: " . $e->getMessage(),
                    $record,
                );
        }
    }

    /**
     * Возвращает найденные записи по тексту
     *
     * @param string $searchTerm
     * @param string|int|null $userId
     *
     * @return array
     */
    public static function search(string $searchTerm, $userId = null): array
    {
        return (new self())->performSearch($searchTerm, $userId);
    }

    /**
     * Возвращает все проиндексированные записи
     *
     * @param array $indices
     * @param int $size
     *
     * @return array
     */
    public static function getAllDocuments(array $indices, int $size = 1000): array
    {
        $self = new self();

        $params = [
            'index' => implode(',', $indices),  // Передаем индексы через запятую
            'scroll' => '1m',  // Устанавливаем время жизни скролла
            'size' => $size,  // Количество документов на одну страницу
            'body' => [
                'query' => [
                    'match_all' => new stdClass(),  // Получаем все документы
                ],
            ],
        ];

        // Выполняем начальный запрос
        $response = $self->client->search($params);

        $scrollId = $response['_scroll_id'];
        $allDocuments = [];

        // Собираем все документы в один массив
        $documentsBatch = $response['hits']['hits'];
        while (count($documentsBatch) > 0) {
            // Добавляем текущую партию документов в массив
            $allDocuments[] = $documentsBatch;

            // Получаем следующую партию данных с помощью Scroll API
            $scrollParams = [
                'scroll_id' => $scrollId,
                'scroll' => '1m',  // Продлеваем время жизни скролла
            ];

            $response = $self->client->scroll($scrollParams);
            $documentsBatch = $response['hits']['hits'];
        }

        // Объединяем все партии данных вне цикла
        return array_merge(...$allDocuments);
    }

    /**
     * Метод для выполнения поиска
     *
     * @param string $searchTerm
     * @param string|int|null $userId
     *
     * @return array
     */
    protected function performSearch(string $searchTerm, $userId = null): array
    {
        // Получаем оригинальный текст и его варианты с транслитерацией
        [$original, $translitLat, $translitCyr] = Translit::make($searchTerm);

        // Строим запросы для поиска по оригинальному тексту и вариантам транслитерации
        $queries = $this->buildQueries($original, $translitLat, $translitCyr, $userId);

        // Выполняем мультипоиск
        $response = $this->client->msearch(['body' => $queries]);

        // Обрабатываем и возвращаем результаты
        return $this->handleResponse($response);
    }

    /**
     * Строим запросы для поиска по нескольким индексам
     *
     * @param string $original
     * @param string $translitLat
     * @param string $translitCyr
     * @param string|int|null $userId
     *
     * @return array
     */
    protected function buildQueries(string $original, string $translitLat, string $translitCyr, $userId = null): array
    {
        //В эластике все индексируется в нижнем регистре (mb_ - требуется для кириллицы)
        $original = mb_strtolower($original);
        $translitLat = mb_strtolower($translitLat);
        $translitCyr = mb_strtolower($translitCyr);

        // Общие параметры для запросов
        $queries = [];

        // Добавляем запросы для каждого индекса
        $queries = array_merge($queries, $this->buildMoscowStocksQuery($original, $translitLat, $translitCyr));
        $queries = array_merge($queries, $this->buildCbCurrenciesQuery($original, $translitLat, $translitCyr));
        $queries = array_merge($queries, $this->buildCustomStocksQuery($original, $translitLat, $translitCyr, $userId));
        $queries = array_merge($queries, $this->buildYahooStocksQuery($original, $translitLat, $translitCyr));
        $queries = array_merge($queries, $this->buildCbondStocksQuery($original, $translitLat, $translitCyr));

        return $queries;
    }

    /**
     * Создание запроса для индекса 'moscow_exchange_stocks'
     *
     * @param string $original
     * @param string $translitLat
     * @param string $translitCyr
     *
     * @return array
     */
    protected function buildMoscowStocksQuery(string $original, string $translitLat, string $translitCyr): array
    {
        // Разбиваем текст на слова
        $words = explode(' ', $original);

        // Берем первое и последнее слово из текста
        $firstWord = $words[0];
        $lastWord = end($words);

        return [
            ['index' => self::MOEX_INDEX],
            [
                'query' => [
                    'bool' => [
                        'should' => [
                            [
                                'multi_match' => [
                                    'query' => $original,
                                    'fields' => ['secid', 'isin', 'name', 'shortname', 'latname'],
                                    'type' => 'best_fields',
                                    'operator' => 'and',
                                ],
                            ],
                            [
                                'multi_match' => [
                                    'query' => $translitLat,
                                    'fields' => ['secid', 'isin', 'name', 'shortname', 'latname'],
                                    'type' => 'best_fields',
                                    'operator' => 'and',
                                ],
                            ],
                            [
                                'multi_match' => [
                                    'query' => $translitCyr,
                                    'fields' => ['secid', 'isin', 'name', 'shortname', 'latname'],
                                    'type' => 'best_fields',
                                    'operator' => 'and',
                                ],
                            ],
                            [
                                'term' => [
                                    'secid.keyword' => strtoupper($firstWord),
                                ],
                            ],
                            [
                                'term' => [
                                    'isin.keyword' => strtoupper($firstWord),
                                ],
                            ],
                            [
                                'term' => [
                                    'secid.keyword' => strtoupper($lastWord),
                                ],
                            ],
                            [
                                'term' => [
                                    'isin.keyword' => strtoupper($lastWord),
                                ],
                            ],
                        ],
                        'must_not' => [
                            [
                                'terms' => [
                                    'type' => ['option_on_shares', 'option'],
                                ],
                            ],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ],
                'size' => 1000,
            ],
        ];
    }


    /**
     * Создание запроса для индекса 'cb_currencies'
     *
     * @param string $original
     * @param string $translitLat
     * @param string $translitCyr
     *
     * @return array
     */
    protected function buildCbCurrenciesQuery(string $original, string $translitLat, string $translitCyr): array
    {
        $words = explode(' ', $original);
        $firstWord = $words[0];
        $lastWord = end($words);

        return [
            ['index' => self::CB_INDEX],
            [
                'query' => [
                    'bool' => [
                        'should' => [
                            [
                                'multi_match' => [
                                    'query' => $original,
                                    'fields' => self::CB_CURRENCIES_FIELDS,
                                    'type' => 'best_fields',
                                    'operator' => 'and',
                                ],
                            ],
                            [
                                'multi_match' => [
                                    'query' => $translitLat,
                                    'fields' => self::CB_CURRENCIES_FIELDS,
                                    'type' => 'best_fields',
                                    'operator' => 'and',
                                ],
                            ],
                            [
                                'multi_match' => [
                                    'query' => $translitCyr,
                                    'fields' => self::CB_CURRENCIES_FIELDS,
                                    'type' => 'best_fields',
                                    'operator' => 'and',
                                ],
                            ],
                            [
                                'term' => [
                                    'char_code.keyword' => strtoupper($firstWord),
                                ],
                            ],
                            [
                                'term' => [
                                    'char_code.keyword' => strtoupper($lastWord),
                                ],
                            ],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ],
                'size' => 1000,
            ],
        ];
    }

    /**
     * Создание запроса для индекса 'custom_stocks' с фильтром по user_id
     *
     * @param string $original
     * @param string $translitLat
     * @param string $translitCyr
     * @param $userId
     *
     * @return array
     */
    protected function buildCustomStocksQuery(
        string $original,
        string $translitLat,
        string $translitCyr,
        $userId = null
    ): array {
        $words = explode(' ', $original);
        $firstWord = $words[0];
        $lastWord = end($words);

        if (is_int($userId)) {
            $userId = config('app.env') . '-' . $userId;
        }

        return [
            ['index' => self::CUSTOM_INDEX],
            [
                'query' => [
                    'bool' => [
                        'should' => [
                            [
                                'multi_match' => [
                                    'query' => $original,
                                    'fields' => self::CUSTOM_STOCKS_FIELDS,
                                    'type' => 'best_fields',
                                    'operator' => 'and',
                                ],
                            ],
                            [
                                'multi_match' => [
                                    'query' => $translitLat,
                                    'fields' => self::CUSTOM_STOCKS_FIELDS,
                                    'type' => 'best_fields',
                                    'operator' => 'and',
                                ],
                            ],
                            [
                                'multi_match' => [
                                    'query' => $translitCyr,
                                    'fields' => self::CUSTOM_STOCKS_FIELDS,
                                    'type' => 'best_fields',
                                    'operator' => 'and',
                                ],
                            ],
                            [
                                'term' => [
                                    'symbol.keyword' => strtoupper($firstWord),
                                ],
                            ],
                            [
                                'term' => [
                                    'symbol.keyword' => strtoupper($lastWord),
                                ],
                            ],
                        ],
                        'filter' => $userId ? [
                            [
                                'term' => [
                                    'user_id.keyword' => $userId,
                                ],
                            ],
                        ] : [],
                        'minimum_should_match' => 1,
                    ],
                ],
                'size' => 1000,
            ],
        ];
    }

    /**
     * Создание запроса для индекса 'yahoo_stocks'
     *
     * @param string $original
     * @param string $translitLat
     * @param string $translitCyr
     *
     * @return array
     */
    protected function buildYahooStocksQuery(string $original, string $translitLat, string $translitCyr): array
    {
        $words = explode(' ', $original);
        $firstWord = $words[0];
        $lastWord = end($words);

        return [
            ['index' => self::YAHOO_INDEX],
            [
                'query' => [
                    'bool' => [
                        'should' => [
                            [
                                'multi_match' => [
                                    'query' => $original,
                                    'fields' => self::YAHOO_STOCKS_FIELDS,
                                    'type' => 'best_fields',
                                    'operator' => 'and',
                                ],
                            ],
                            [
                                'multi_match' => [
                                    'query' => $translitLat,
                                    'fields' => self::YAHOO_STOCKS_FIELDS,
                                    'type' => 'best_fields',
                                    'operator' => 'and',
                                ],
                            ],
                            [
                                'multi_match' => [
                                    'query' => $translitCyr,
                                    'fields' => self::YAHOO_STOCKS_FIELDS,
                                    'type' => 'best_fields',
                                    'operator' => 'and',
                                ],
                            ],
                            [
                                'term' => [
                                    'symbol.keyword' => strtoupper($firstWord),
                                ],
                            ],
                            [
                                'term' => [
                                    'symbol.keyword' => strtoupper($lastWord),
                                ],
                            ],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ],
                'size' => 1000,
            ],
        ];
    }

    /**
     * Создание запроса для индекса 'cbond_stocks'
     *
     * @param string $original
     * @param string $translitLat
     * @param string $translitCyr
     *
     * @return array
     */
    protected function buildCbondStocksQuery(string $original, string $translitLat, string $translitCyr): array
    {
        $words = explode(' ', $original);
        $firstWord = $words[0];
        $lastWord = end($words);

        return [
            ['index' => self::CBOND_INDEX],
            [
                'query' => [
                    'bool' => [
                        'should' => [
                            [
                                'multi_match' => [
                                    'query' => $original,
                                    'fields' => self::CBOND_STOCKS_FIELDS,
                                    'type' => 'best_fields',
                                    'operator' => 'and',
                                ],
                            ],
                            [
                                'multi_match' => [
                                    'query' => $translitLat,
                                    'fields' => self::CBOND_STOCKS_FIELDS,
                                    'type' => 'best_fields',
                                    'operator' => 'and',
                                ],
                            ],
                            [
                                'multi_match' => [
                                    'query' => $translitCyr,
                                    'fields' => self::CBOND_STOCKS_FIELDS,
                                    'type' => 'best_fields',
                                    'operator' => 'and',
                                ],
                            ],
                            [
                                'term' => [
                                    'secid.keyword' => strtoupper($firstWord),
                                ],
                            ],
                            [
                                'term' => [
                                    'secid.keyword' => strtoupper($lastWord),
                                ],
                            ],
                            [
                                'term' => [
                                    'isin.keyword' => strtoupper($firstWord),
                                ],
                            ],
                            [
                                'term' => [
                                    'isin.keyword' => strtoupper($lastWord),
                                ],
                            ],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ],
                'size' => 1000,
            ],
        ];
    }

    /**
     * @param array $hit
     *
     * @return array
     * @throws Exception
     */
    public static function getItemDataFromHit(array $hit): array
    {
        // Определяем маппинг индексов к моделям
        $indexToModelMap = [
            self::MOEX_INDEX => MoscowExchangeStock::class,
            self::CBOND_INDEX => CbondStock::class,
            self::CB_INDEX => CbCurrency::class,
            self::CUSTOM_INDEX => CustomStock::class,
            self::YAHOO_INDEX => YahooStock::class,
        ];

        // Получаем название индекса из хита
        $index = $hit['_index'];

        // Проверяем, есть ли соответствующая модель
        if (!isset($indexToModelMap[$index])) {
            throw new Exception("Модель для индекса $index не найдена");
        }

        // Инициализируем соответствующую модель
        $modelClass = $indexToModelMap[$index];
        // Инициализируем модель данными из документа
        $model = new $modelClass($hit['_source']);

        // Возвращаем данные, вызвав метод getItemData
        return $model->getItemData();
    }

    /**
     * Обрабатываем и возвращаем результаты
     *
     * @param array $response
     *
     * @return array
     */
    protected function handleResponse(array $response): array
    {
        // Обрабатываем результаты из всех индексов
        $results = [];
        foreach ($response['responses'] as $resp) {
            if (isset($resp['hits']['hits'])) {
                // Если есть результаты поиска, добавляем их
                $results[] = $resp['hits']['hits'];
            } elseif (isset($resp['error'])) {
                // Если произошла ошибка, добавляем информацию об ошибке
                LoggerHelper::getLogger(class_basename($this))->error(
                    'Elasticsearch error: ' . json_encode($resp['error']),
                );
            }
        }

        // Возвращаем плоский массив результатов
        return !empty($results) ? array_merge(...$results) : [];
    }

    /**
     * Удаляет запись из указанного индекса Elasticsearch
     *
     * @param string $index Название индекса
     * @param string|int $id Идентификатор записи для удаления
     *
     * @return bool Возвращает true, если удаление прошло успешно или запись отсутствует, иначе false
     */
    public static function deleteFromIndex(string $index, $id): bool
    {
        $self = new self();

        try {
            // Проверяем существование записи в индексе
            $exists = $self->client->exists([
                'index' => $index,
                'id' => $id,
            ]);

            if (!$exists) {
                // Возвращаем true, так как нечего удалять
                return true;
            }

            // Выполняем удаление записи из индекса Elasticsearch
            $self->client->delete([
                'index' => $index,
                'id' => $id,
            ]);

            return true;
        } catch (Exception $e) {
            // Логируем ошибку при удалении записи
            LoggerHelper::getLogger()
                ->error("Ошибка при удалении записи с ID $id из индекса $index: " . $e->getMessage());

            return false;
        }
    }
}
