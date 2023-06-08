<?php

namespace Common\Models\Catalog\TradingView;

use Common\Models\Catalog\BaseCatalog;
use Common\Models\Interfaces\Catalog\TradingView\DefinitionTradingViewConst;

/**
* Class TradingViewTicker
*
* @property $symbol
* @property $description
* @property $exchange
* @property $provider_id
* @property $country
* @property $typespecs
* @property $industry_id
* @property $type
* @property $point_value
* @property $exchange_web
* @property $listed_exchange
* @property $currency
* @property $tick_size
* @property $sector
* @property $industry
* @property $timezone
* @property $session
* @property $icon
* @property $update_history
* @property $capitalization
* @property $is_parse
 **/
class TradingViewTicker extends BaseCatalog implements DefinitionTradingViewConst
{
    //Связи с другими моделями
    use \Common\Models\Traits\Catalog\TradingView\TradingViewRelationshipsTrait;

    //Возвращаемые данные для трансформеров, текущей сущности и тп
    use \Common\Models\Traits\Catalog\TradingView\TradingViewScopeTrait;

    //функции запросов
    use \Common\Models\Traits\Catalog\TradingView\TradingViewReturnGetDataFunc;

    /**
     * @var string
     */
    public $table = 'tv_tickers';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'symbol',
        'description',
        'exchange',
        'provider_id',
        'country',
        'typespecs',
        'industry_id',
        'type',
        'point_value',
        'exchange_web',
        'listed_exchange',
        'currency',
        'tick_size',
        'sector',
        'industry',
        'timezone',
        'session',
        'icon',
        'update_history',
        'parent_id',
        'capitalization',
        'ru_industry',
        'ru_sector',
        'average_volume',
        'is_parse',
    ];

    protected $casts = [
        'symbol' => 'string',
        'description' => 'string',
        'exchange' => 'string',
        'provider_id' => 'string',
        'country' => 'string',
        'typespecs' => 'text',
        'industry_id' => 'integer',
        'type' => 'string',
        'point_value' => 'integer',
        'exchange_web' => 'string',
        'listed_exchange' => 'string',
        'currency' => 'string',
        'tick_size' => 'text',
        'sector' => 'string',
        'industry' => 'string',
        'timezone' => 'string',
        'session' => 'string',
        'icon' => 'string',
        'update_history' => 'date',
        'parent_id' => 'integer',
        'capitalization' => 'integer',
        'ru_industry' => 'string',
        'ru_sector' => 'string',
        'average_volume' => 'double',
        'is_parse' => 'integer?',
    ];
}
