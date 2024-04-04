<?php
namespace Common\Models\Traits\Users\Roles\Client;

use App\Helpers\Active\ActiveHelper;
use App\Models\Actives\Active;
use App\Models\Actives\ActiveGoal;
use App\Models\Actives\ActiveTrade;

/**
 * Trait StrategyTrait
 *
 * @mixin \Common\Models\Users\Roles\Types\Client
 *
 * @package App\Models\Traits
 */
trait ValueTrait
{
    /**
     * @param $currency
     * @param $nowDate
     * @param $endDate
     * @return array
     */
    public function getChartValue($currency, $nowDate, $endDate)
    {
        $actives = $this
            ->actives()
            ->with('trades_ordered', 'trades_ordered.currency')
            ->with('buy_trades', 'buy_trades.currency')
            ->with('sell_trades', 'sell_trades.currency')
            ->with('dividends', 'dividends.currency')
            ->with('valuations', 'valuations.currency')
            ->with('payments', 'payments.currency')
            ->with('sell_currency')
            ->with('buy_currency')
            ->with('sell_account')
            ->with('buy_account')
            ->with('coupons')
            ->with('last_coupon')
            ->whereIntegerInRaw('type_id', Active::INVEST_GROUP)
            ->get();

        $data = [];

        $i = 0;
        while ($nowDate->lessThan($endDate)) 
        {
            $points = [];

            $data[$i] = [
                'index' => $i,
                'category' => $nowDate->format('Y-m-d'),
                'value' => 0,
                'points' => []
            ];

            if ($i != 0)
            {
                $prev = $i - 1;
                $data[$i]['value'] += $data[$prev]['value'];
            }

            /**
             * @var Active $active
             */
            foreach ($actives as $active)
            {
                $data[$i]['value'] += $active->getInvest($nowDate->copy(), $currency, $points);
            }

            $data[$i]['points'] = $points;

            $nowDate->addMonth()->startOfDay();
            $i++;
        }

        return $data;
    }

    /**
     * @param $goalIds
     * @param $currency
     * @return array
     */
    public function getChartValueByGoals($goalIds, $currency)
    {
        $data = [];

        $goals = $this->goals()
            ->whereIntegerInRaw('id', $goalIds)
            ->with('all_items', 'all_items.item', 'all_items.item.active')
            ->get();

        $allActives = [];

        /**
         * @var ActiveGoal[] $goals
         */
        foreach ($goals as $k => &$goal)
        {
            $allActives[$k]['actives'] = [];
            /**
             * @var Active|ActiveTrade $item
             */
            foreach ($goal->all_items as &$item)
            {
                if($item->item->getMorphClass() === 'active')
                {
                    $active = $item->item;
                }else if($item->item->getMorphClass() === 'active.trade'){
                    $active = $item->item->active;
                    $active->buy_trades = [$item->item];
                }

                $allActives[$k]['actives'][] = $active;
            }
        }

        foreach ($allActives as $k => &$array)
        {
            $allActives[$k]['earliestDate'] = ActiveHelper::getEarliestDate($array['actives'])->startOfDay()->startOfMonth();
            $allActives[$k]['oldestDate'] = ActiveHelper::getOldestDate($array['actives'])->startOfDay()->startOfMonth();
        }

        foreach ($goals as $k => &$goal)
        {
            $i = 0;
            $nowDate = $allActives[$k]['earliestDate'];
            $endDate = $allActives[$k]['oldestDate'];

            while ($nowDate->lessThan($endDate))
            {
                $points = [];

                $data[$goal->id][$i] = [
                    'index' => $i,
                    'category' => $nowDate->format('Y-m-d'),
                    'value' => 0,
                    'points' => []
                ];

                if ($i != 0)
                {
                    $prev = $i - 1;
                    $data[$goal->id][$i]['value'] += $data[$goal->id][$prev]['value'];
                }

                /**
                 * @var Active $active
                 */
                foreach ($allActives[$k]['actives'] as $active)
                {
                    $data[$goal->id][$i]['value'] += $active->getInvest($nowDate->copy(), $currency, $points);
                }

                $data[$goal->id][$i]['points'] = $points;

                $nowDate->addMonth()->startOfDay();
                $i++;
            }
        }

        return $data;
    }
}