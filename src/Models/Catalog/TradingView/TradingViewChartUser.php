<?php

namespace Common\Models\Catalog\TradingView;

use Common\Models\Catalog\BaseStock;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property $ticker_id
 * @property $user_id
 */
class TradingViewChartUser extends BaseStock
{
    /**
     * @var string
     */
    public $table = 'tv_chart_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ticker_id',
        'user_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'ticker_id' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * @return HasOne
     */
    public function ticker(): HasOne
    {
        return $this->hasOne(TradingViewTicker::class, 'id', 'ticker_id');
    }

//    /**
//     * @return HasOne
//     */
//    public function user(): HasOne
//    {
//        return $this->hasOne(User::class, 'id', 'user_id');
//    }

    /**
     * @param $tickerId
     * @param $userId
     * @return void
     */
    public static function saveTicker($tickerId, $userId)
    {
        self::where('ticker_id', $tickerId)
            ->where('user_id', $userId)
            ->delete();

        if ($tickerId)
        {
            return self::create([
                'ticker_id' => $tickerId,
                'user_id' => $userId,
            ]);
        }
    }
}
