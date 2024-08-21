<?php
namespace Common\Models\Traits\Users;

use App\Helpers\Active\ActiveHelper;
use App\Models\Accounts\UserAccountCurrency;
use App\Models\Actives\Active;
use Cache;
use Carbon\Carbon;
use Common\Helpers\LoggerHelper;
use Common\Jobs\JobsEvent;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;
use Common\Models\Users\Roles\Types\Client;
use Exception;

/**
 * Trait StrategyTrait
 *
 * @mixin Client
 *
 * @package App\Models\Traits
 */
trait StrategyTrait
{
    /**
     * @param $typeIds
     * @param JobsEvent|null $event
     *
     * @return array
     * @throws Exception
     */
    public function getChartStrategy($typeIds, ?JobsEvent $event = null): array
    {
        $incomeCategory = 1;
        $investCategory = 2;
        $propertyCategory = 3;
        $outcomeCategory = 4;
        $investLoseCategory = 5;
        $propertyLoseCategory = 6;

        if(empty($this->birth_at))
        {
            throw new Exception(__('model.strategy.chart_strategy.birth_at'));
        }
        if(empty($this->start_enter_at))
        {
            throw new Exception(__('model.strategy.chart_strategy.start_enter_at'));
        }


        /**
         * @var Active[] $actives
         */
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
            ->where(function ($query)
            {
                $query->where('user_id', $this->id)
                    ->where('type_id', '!=', Active::CURRENCY);
            })
            ->orWhere(function($query){
                $query->where('action_id', Active::GET)
                    ->where('user_id', $this->id)
                    ->where('type_id', '!=', Active::CURRENCY);
            })
            ->get();

        $transfers = $this
            ->transfers()
            ->get();

        $userAccounts = $this
            ->user_accounts()
            ->with([
                'accounts',
                'accounts.income_logs',
                'accounts.outcome_logs',
                'accounts.income_logs.item',
                'accounts.outcome_logs.item',
                'accounts.income_logs.item.from_account',
                'accounts.income_logs.item.to_account',
                'accounts.outcome_logs.item.from_account',
                'accounts.outcome_logs.item.to_account',
                'accounts.income_logs.item.from_account.user_account',
                'accounts.income_logs.item.to_account.user_account',
                'accounts.outcome_logs.item.from_account.user_account',
                'accounts.outcome_logs.item.to_account.user_account',
            ])
            ->get();

        $data = [];

        //первая дата и конечная дата
        $startDate = ActiveHelper::getEarliestDate($actives, $transfers)->startOfMonth()->subYear();
        $endDate = Carbon::now()->endOfMonth();

        LoggerHelper::getLogger('debug')->info('-----------------------------------------------------------------------');
        LoggerHelper::getLogger('debug')->info('$startDate ' . $startDate->format('Y-m-d'));
        LoggerHelper::getLogger('debug')->info('$endDate ' . $endDate->format('Y-m-d'));

        $this->addPoint(Carbon::now(), '#ff0000', __('model.strategy.chart_strategy.today'));
        $this->addPoint($this->birth_at->copy()->addYears($this->retired_age), '#7B7B7B', __('model.strategy.chart_strategy.career'));

        $i = 0;
        $lose = [];

        $activesCacheData = [];

        $count = 0;
        $sizeActives = count($actives);
        $sizeAccounts = count($userAccounts);
        $diffMonths = $startDate->diffInMonths($endDate);
        $size = $sizeActives + $sizeAccounts + $diffMonths;

        $datesIndex = [];
        $startDateCopy = $startDate->copy();
        $endDateCopy = $endDate->copy();
        while ($startDateCopy->lessThan($endDateCopy))
        {
            $datesIndex[$startDateCopy->format('Y-m-d')] = $i;
            $startDateCopy->addMonth()->startOfDay();
            $i++;
        }

        for ($n = 0; $n < $sizeActives; $n++) {
            $start_time = microtime(true);
            if ($event) {
                $event->processing($count, $size);
            }

            /**
             * @var Active[] $actives
             */
            $activesCacheData[$n] = $actives[$n]->cacheStrategy($startDate->copy(), $endDate->copy(), $datesIndex);

            $end_time = microtime(true);

            LoggerHelper::getLogger('debug')->info('---------------active');
            LoggerHelper::getLogger('debug')->info($actives[$n]->id);
            LoggerHelper::getLogger('debug')->info($end_time - $start_time);
            $count++;
        }

        $accountCacheData = [];
        foreach ($userAccounts as $key => $userAccount)
        {
            if ($event) {
                $event->processing($count, $size);
            }

            $start_time = microtime(TRUE);

            $accounts = $userAccount->accounts;

            if($accounts->count())
            {
                /**
                 * @var UserAccountCurrency $account
                 */
                foreach ($accounts as $subKey => $account)
                {
                    $accountCacheData[$key][$subKey] = $account->cacheStrategy($startDate->copy(), $endDate->copy(), $datesIndex);
                }
            }

            $end_time = microtime(TRUE);

            LoggerHelper::getLogger('debug')->info('---------------account');
            LoggerHelper::getLogger('debug')->info($userAccount->id);
            LoggerHelper::getLogger('debug')->info($end_time - $start_time);
            $count++;
        }

        $i = 0;
        while ($startDate->lessThan($endDate))
        {
            if ($event) {
                $event->processing($count, $size);
            }

            $start_time = microtime(TRUE);

            $data[$i] = [
                'index' => $i,
                'category' => $startDate->format('Y-m-d'),
                'income' => 0,
                'income_percent' => 0,
                'property' => 0,
                'property_percent' => 0,
                'invest' => 0,
                'invest_percent' => 0,
                'outcome' => 0,
                'outcome_percent' => 0,
                'property_lose' => 0,
                'property_lose_percent' => 0,
                'invest_lose' => 0,
                'invest_lose_percent' => 0,
//                'debug' => []
            ];

            $sum = 0;
            if ($i != 0)
            {
                $prev = $i - 1;
                $data[$i]['income'] += $data[$prev]['income'];
                $data[$i]['property'] += $data[$prev]['property'];
                $data[$i]['invest'] += $data[$prev]['invest'];
                $data[$i]['outcome'] += $data[$prev]['outcome'];
                $data[$i]['property_lose'] += $data[$prev]['property_lose'];
                $data[$i]['invest_lose'] += $data[$prev]['invest_lose'];


                $sum += $data[$prev]['income'];
                $sum += $data[$prev]['property'];
                $sum += $data[$prev]['invest'];
                $sum += $data[$prev]['outcome'];
                $sum += $data[$prev]['property_lose'];
                $sum += $data[$prev]['invest_lose'];
            }

            foreach ($userAccounts as $key =>  $userAccount)
            {
                $accounts = $userAccount->accounts;

                if($accounts->count())
                {
                    /**
                     * @var UserAccountCurrency $account
                     */
                    foreach ($accounts as $subKey => $account)
                    {
                        /**
                         * @var UserAccountCurrency $account
                         */
                        if(in_array($incomeCategory, $typeIds) && isset($accountCacheData[$key][$subKey][$i]['income']))
                        {
                            $data[$i]['income'] += $accountCacheData[$key][$subKey][$i]['income'];
                        }

                        if(in_array($outcomeCategory, $typeIds) && isset($accountCacheData[$key][$subKey][$i]['invest']))
                        {
                            $data[$i]['invest'] += $accountCacheData[$key][$subKey][$i]['invest'];
                        }

                        if(in_array($investCategory, $typeIds) && isset($accountCacheData[$key][$subKey][$i]['outcome']))
                        {
                            $data[$i]['outcome'] -= abs($accountCacheData[$key][$subKey][$i]['outcome']);
                        }
                    }
                }
            }

            $size = count($actives);
            for ($n = 0 ; $n < $size; $n++)
            {
                /**
                 * @var Active $actives[]
                 */

                if($activesCacheData)
                {

                    $income = $activesCacheData[$n][$i]['income'] ?? 0;
                    $outcome = $activesCacheData[$n][$i]['outcome'] ?? 0;
                    $property = $activesCacheData[$n][$i]['property'] ?? 0;
                    $invest = $activesCacheData[$n][$i]['invest'] ?? 0;
                    $outcomePropertyLose = $activesCacheData[$n][$i]['property_lose'] ?? 0;
                    $outcomeInvestLose = $activesCacheData[$n][$i]['invest_lose'] ?? 0;


                    if(in_array($incomeCategory, $typeIds))
                    {
                        $data[$i]['income'] += $income - abs($outcome);
                    }
                    if(in_array($propertyCategory, $typeIds))
                    {
                        $data[$i]['property'] += $property;
                    }
                    if(in_array($investCategory, $typeIds))
                    {
                        $data[$i]['invest'] += $invest;
                    }
                    if(in_array($outcomeCategory, $typeIds))
                    {
                        $data[$i]['outcome'] -= abs($outcome);
                    }
                    if(in_array($investLoseCategory, $typeIds))
                    {
                        $data[$i]['property_lose'] -= abs($outcomePropertyLose);
                    }
                    if(in_array($propertyLoseCategory, $typeIds))
                    {
                        $data[$i]['invest_lose'] -= abs($outcomeInvestLose);
                    }

                    $sum += $income + $property + $invest;// + $outcome + $outcomeInvestLose + $outcomePropertyLose;
//
//                $data[$i]['debug'][] = [
//                    'active_id' => $actives[$n]->id,
//                    'income' => $income,
//                    'active' => $property,
//                    'outcome' => $outcome,
//                    'property_lose' => $outcomePropertyLose,
//                    'invest_lose' => $outcomeInvestLose,
//                    'invest' => $invest,
//                    'sum' => $sum,
//                    'retrieve_income' => $data[$i]['income'] < 0
//                ];

//                if($outcomeHighlight != 0)
//                {
//                    $lose[] = [
//                        'outcome_lose' => $outcomeHighlight,
//                        'date' => $nowDate->format('Y-m-d'),
//                    ];
//                }


                }
            }


            //если у нас есть покупки превышающие сумму накопления,
            //то мы добавляем к начальной точке недостающую сумму
            if ($data[$i]['income'] < 0)
            {
                $data[$i]['income'] = 0;
//                $diff = abs($data[$i]['income']);
//                foreach ($data as $k => &$item)
//                {
//                    //добавляем до даты, на которой минус,
//                    //чтобы сгладить появления инвестиции
//                    if($k <= $i)
//                    {
//                        $data[$k]['income'] += $diff;
//                    }
//                }
            }

            $data[$i]['income_percent'] = ($sum > 0) ? round($data[$i]['income'] * 100 / $sum, 2, PHP_ROUND_HALF_DOWN) : 0;
            $data[$i]['property_percent'] = ($sum > 0) ? round($data[$i]['property'] * 100 / $sum, 2, PHP_ROUND_HALF_DOWN) : 0;
            $data[$i]['invest_percent'] = ($sum > 0) ? round($data[$i]['invest'] * 100 / $sum, 2, PHP_ROUND_HALF_DOWN) : 0;
            $data[$i]['property_lose_percent'] = ($sum > 0) ? round($data[$i]['property_lose'] * 100 / $sum, 2, PHP_ROUND_HALF_DOWN) : 0;
            $data[$i]['outcome_percent'] = ($sum > 0) ? round($data[$i]['outcome'] * 100 / $sum, 2, PHP_ROUND_HALF_DOWN) : 0;
            $data[$i]['invest_lose_percent'] = ($sum > 0) ? round($data[$i]['invest_lose'] * 100 / $sum, 2, PHP_ROUND_HALF_DOWN) : 0;


            $startDate->addMonth()->startOfDay();
            $i++;

            $end_time = microtime(TRUE);

            LoggerHelper::getLogger('debug')->info('---------------iterate');
            LoggerHelper::getLogger('debug')->info($startDate->format('Y-m-d'));
            LoggerHelper::getLogger('debug')->info($end_time - $start_time);

            $count++;
        }


//        //округляем цифры
//        foreach ($data as $key => &$item)
//        {
//            $item['income'] = round($item['income'], 2, PHP_ROUND_HALF_DOWN);
//            $item['property'] = round($item['property'], 2, PHP_ROUND_HALF_DOWN);
//            $item['invest'] = round($item['invest'], 2, PHP_ROUND_HALF_DOWN);
//            $item['outcome'] = round($item['outcome'], 2, PHP_ROUND_HALF_DOWN);
//            $item['property_lose'] = round($item['property_lose'], 2, PHP_ROUND_HALF_DOWN);
//            $item['invest_lose'] = round($item['invest_lose'], 2, PHP_ROUND_HALF_DOWN);
//
//            if(empty($data[$key]['income']))
//            {
//                unset($data[$key]['income']);
//            }
//            if(empty($data[$key]['income_percent']))
//            {
//                unset($data[$key]['income_percent']);
//            }
//            if(empty($data[$key]['property']))
//            {
//                unset($data[$key]['property']);
//            }
//            if(empty($data[$key]['property_percent']))
//            {
//                unset($data[$key]['property_percent']);
//            }
//            if(empty($data[$key]['invest']))
//            {
//                unset($data[$key]['invest']);
//            }
//            if(empty($data[$key]['invest_percent']))
//            {
//                unset($data[$key]['invest_percent']);
//            }
//            if(empty($data[$key]['outcome']))
//            {
//                unset($data[$key]['outcome']);
//            }
//            if(empty($data[$key]['outcome_percent']))
//            {
//                unset($data[$key]['outcome_percent']);
//            }
//            if(empty($data[$key]['property_lose']))
//            {
//                unset($data[$key]['property_lose']);
//            }
//            if(empty($data[$key]['property_lose_percent']))
//            {
//                unset($data[$key]['property_lose_percent']);
//            }
//            if(empty($data[$key]['invest_lose']))
//            {
//                unset($data[$key]['invest_lose']);
//            }
//            if(empty($data[$key]['invest_lose_percent']))
//            {
//                unset($data[$key]['invest_lose_percent']);
//            }
//        }

        return $data;
    }
}