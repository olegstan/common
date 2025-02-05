<?php

namespace Common\Models\Catalog\Tinkoff;

use Common\Models\Catalog\BaseCatalog;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property $name
 * @property $figi
 * @property $ticker
 * @property $class_code
 * @property $isin
 * @property $lot
 * @property $currency
 * @property $klong
 * @property $kshort
 * @property $dlong
 * @property $dshort
 * @property $dlong_min
 * @property $dshort_min
 * @property $short_enabled_flag
 * @property $exchange
 * @property $ipo_date
 * @property $issue_size
 * @property $country_of_risk
 * @property $country_of_risk_name
 * @property $sector
 * @property $issue_size_plan
 * @property $nominal
 * @property $trading_status
 * @property $otc_flag
 * @property $buy_available_flag
 * @property $sell_available_flag
 * @property $div_yield_flag
 * @property $share_type
 * @property $min_price_increment
 * @property $api_trade_available_flag
 * @property $uid
 * @property $real_exchange
 * @property $position_uid
 * @property $for_iis_flag
 * @property $for_qual_investor_flag
 * @property $weekend_flag
 * @property $blocked_tca_flag
 * @property $liquidity_flag
 * @property $first_1min_candle_date
 * @property $first_1day_candle_date
 * @property $coupon_quantity_per_year
 * @property $maturity_date
 * @property $initial_nominal
 * @property $state_reg_date
 * @property $placement_date
 * @property $placement_price
 * @property $aci_value
 * @property $issue_kind
 * @property $floating_coupon_flag
 * @property $perpetual_flag
 * @property $amortization_flag
 * @property $subordinated_flag
 * @property $risk_level
 * @property $fixed_commission
 * @property $focus_type
 * @property $released_date
 * @property $num_shares
 * @property $rebalancing_freq
 * @property $asset_type
 * @property $basic_asset
 * @property $basic_asset_size
 * @property $basic_asset_position_uid
 * @property $type
 */
class TinkoffStock extends BaseCatalog
{
    use HasFactory;
    
    /**
     * @var string
     */
    public $table = 'tinkoff_stocks';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'figi',
        'ticker',
        'class_code',
        'isin',
        'lot',
        'currency',
        'klong',
        'kshort',
        'dlong',
        'dshort',
        'dlong_min',
        'dshort_min',
        'short_enabled_flag',
        'exchange',
        'ipo_date',
        'issue_size',
        'country_of_risk',
        'country_of_risk_name',
        'sector',
        'issue_size_plan',
        'nominal',
        'trading_status',
        'otc_flag',
        'buy_available_flag',
        'sell_available_flag',
        'div_yield_flag',
        'share_type',
        'min_price_increment',
        'api_trade_available_flag',
        'uid',
        'real_exchange',
        'position_uid',
        'for_iis_flag',
        'for_qual_investor_flag',
        'weekend_flag',
        'blocked_tca_flag',
        'liquidity_flag',
        'first_1min_candle_date',
        'first_1day_candle_date',
        'coupon_quantity_per_year',
        'maturity_date',
        'initial_nominal',
        'state_reg_date',
        'placement_date',
        'placement_price',
        'aci_value',
        'issue_kind',
        'floating_coupon_flag',
        'perpetual_flag',
        'amortization_flag',
        'subordinated_flag',
        'risk_level',
        'fixed_commission',
        'focus_type',
        'released_date',
        'num_shares',
        'rebalancing_freq',
        'asset_type',
        'basic_asset',
        'basic_asset_size',
        'basic_asset_position_uid',
        'type',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'figi' => 'string',
        'ticker' => 'string',
        'class_code' => 'string',
        'isin' => 'string',
        'lot' => 'integer',
        'currency' => 'string',
        'klong' => 'double',
        'kshort' => 'double',
        'dlong' => 'double',
        'dshort' => 'double',
        'dlong_min' => 'double',
        'dshort_min' => 'double',
        'short_enabled_flag' => 'boolean',
        'exchange' => 'string',
        'ipo_date' => 'datetime',
        'issue_size' => 'integer',
        'country_of_risk' => 'string',
        'country_of_risk_name' => 'string',
        'sector' => 'string',
        'issue_size_plan' => 'integer',
        'nominal' => 'double',
        'trading_status' => 'integer',
        'otc_flag' => 'boolean',
        'buy_available_flag' => 'boolean',
        'sell_available_flag' => 'boolean',
        'div_yield_flag' => 'boolean',
        'share_type' => 'integer',
        'min_price_increment' => 'double',
        'api_trade_available_flag' => 'boolean',
        'uid' => 'string',
        'real_exchange' => 'integer',
        'position_uid' => 'string',
        'for_iis_flag' => 'boolean',
        'for_qual_investor_flag' => 'boolean',
        'weekend_flag' => 'boolean',
        'blocked_tca_flag' => 'boolean',
        'liquidity_flag' => 'boolean',
        'first_1min_candle_date' => 'datetime',
        'first_1day_candle_date' => 'datetime',
        'coupon_quantity_per_year' => 'integer',
        'maturity_date' => 'datetime',
        'initial_nominal' => 'double',
        'state_reg_date' => 'datetime',
        'placement_date' => 'datetime',
        'placement_price' => 'double',
        'aci_value' => 'double',
        'issue_kind' => 'string',
        'floating_coupon_flag' => 'boolean',
        'perpetual_flag' => 'boolean',
        'amortization_flag' => 'boolean',
        'subordinated_flag' => 'boolean',
        'risk_level' => 'integer',
        'fixed_commission' => 'double',
        'focus_type' => 'string',
        'released_date' => 'datetime',
        'num_shares' => 'double',
        'rebalancing_freq' => 'string',
        'asset_type' => 'string',
        'basic_asset' => 'string',
        'basic_asset_size' => 'double',
        'basic_asset_position_uid' => 'string',
        'type' => 'string',
    ];

    public $timestamps = false;

    /**
     * @return HasMany
     */
    public function dividends(): HasMany
    {
        return $this->hasMany(TinkoffDividend::class, 'tinkoff_stock_id');
    }

    /**
     * @return HasMany
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(TinkoffCoupon::class, 'tinkoff_stock_id');
    }

    /**
     * @return mixed
     */
    public function getSymbol()
    {
        return $this->ticker;
    }

    /**
     * @return mixed
     */
    public function getSecondSymbol()
    {
        return $this->isin;
    }
}
