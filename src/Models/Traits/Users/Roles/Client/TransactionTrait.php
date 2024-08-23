<?php
namespace Common\Models\Traits\Users\Roles\Client;

use App\Models\Accounts\UserAccount;
use App\Models\Accounts\UserSubaccount;
use App\Models\Actives\Active;
use App\Models\Actives\ActiveGoalPayment;
use App\Models\Actives\ActivePayment;
use App\Models\CreditLog;
use App\Models\Transfers\Transfer;
use Carbon\Carbon;
use Common\Models\Currency;
use Common\Models\Users\Roles\Types\Client;
use Exception;

/**
 * Trait TransactionTrait
 *
 * @mixin Client
 *
 * @package App\Models\Traits
 */
trait TransactionTrait
{
    /**
     * @param Currency $currency
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param $step
     * @param $accountIds
     * @param $typeIds
     * @return array
     * @throws Exception
     */
    public function getChartTransaction($currency, Carbon $startDate, Carbon $endDate, $step, $accountIds, $typeIds)
    {
        if(empty($this->start_enter_at))
        {
            throw new Exception(__('model.transactions.chart_transaction.start_enter_at'));
        }

        $data = [];

        $i = 0;
        $nowDate = $startDate->copy();

        $this->addPoint(Carbon::now(), '#ff0000', __('model.transactions.chart_transaction.today'));
        $this->addPoint($this->start_enter_at, '#ff0000', __('model.transactions.chart_transaction.date_data'));



        //TODO оптимизировать запрос, сразу запросить по всем датам логи
        while ($nowDate->lessThan($endDate))
        {
            $data[$i] = [
                'index' => $i,
                'category' => $nowDate->format('Y-m'),
                'income' => 0,
                'income_invest' => 0,
                'income_property' => 0,
                'plan_income' => 0,
                'income_diff' => 0,
                'property' => 0,
                'yellow_invest' => 0,
                'outcome' => 0,
                'outcome_invest' => 0,
                'outcome_property' => 0,
                'plan_outcome' => 0,
                'plan_outcome_invest' => 0,
                'outcome_diff' => 0,
                'grey_undefined' => 0,
                'debug' => []
            ];

            $income = 0;
            $planIncome = 0;

            $typeIds = $typeIds ?: [];

            if($typeIds)
            {
                foreach ($typeIds as $typeId)
                {
                    switch ($typeId)
                    {
                        case CreditLog::SALARY:
                            $income += $this->getTransactionsChartIncome($nowDate->copy(), $currency, $accountIds);
                            $planIncome += $this->getTransactionsChartPlanIncome($nowDate->copy(), $currency, $accountIds);
                            break;
                        case CreditLog::BROKER:
                            $income += $this->getTransactionsChartIncomeInvest($nowDate->copy(), $currency, $accountIds);
                            break;
                        case CreditLog::RENT:
                            $income += $this->getTransactionsChartRentIncome($nowDate->copy(), $currency, $accountIds);
                            break;
                        case CreditLog::SELL_PROPERTY:
                            $income += $this->getTransactionsChartIncomeProperty($nowDate->copy(), $currency, $accountIds);
                            break;
                    }
                }
            }

            $outcome = $this->getTransactionsChartOutcome($nowDate->copy(), $currency, $accountIds);
            $outcomeInvest = $this->getTransactionsChartOutcomeInvest($nowDate->copy(), $currency, $accountIds);
            $outcomeProperty = $this->getTransactionsChartOutcomeProperty($nowDate->copy(), $currency, $accountIds);

            $planOutcome = $this->getTransactionsChartPlanOutcome($nowDate->copy(), $currency, $accountIds);
            $planOutcomeInvest = $this->getTransactionsChartPlanOutcomeInvest($nowDate->copy(), $currency, $accountIds);


            $diff = (abs($outcome) + abs($outcomeInvest) + abs($outcomeProperty)) - $income;
            $undefined  = $diff > 0 ? $diff : 0;

            $data[$i]['income'] = $income;
            $data[$i]['plan_income'] = $planIncome;
            $data[$i]['outcome'] = abs($outcome);
            $data[$i]['outcome_invest'] = abs($outcomeInvest);
            $data[$i]['outcome_property'] = abs($outcomeProperty);
            $data[$i]['plan_outcome'] = abs($planOutcome);
            $data[$i]['plan_outcome_invest'] = abs($planOutcomeInvest);

            $diffIncome = $planIncome - $income;
            $data[$i]['income_diff'] = $diffIncome > 0 ? $diffIncome : 0;

            $diffOutcome = $planOutcome - $outcome;
            $data[$i]['outcome_diff'] = $diffOutcome > 0 ? $diffOutcome : 0;


            $nowDate->addMonth();
            $i++;
        }

        return $data;
    }

    /**
     * @param $currency
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param $step
     * @param $accountIds
     * @param $typeIds
     * @return array
     * @throws Exception
     */
    public function getChartTotalTransaction($currency, Carbon $startDate, Carbon $endDate, $step, $accountIds, $typeIds)
    {
        if(empty($this->start_enter_at))
        {
            throw new Exception(__('model.transactions.chart_total_transaction.start_enter_at'));
        }

        $data = [];

        $i = 0;
        $nowDate = Carbon::now();
        $helpDate = $startDate->copy();

        $this->addPoint(Carbon::now(), '#ff0000', __('model.transactions.chart_total_transaction.today'));
        $this->addPoint($this->start_enter_at, '#ff0000', __('model.transactions.chart_total_transaction.date_data'));

        //TODO оптимизировать запрос, сразу запросить по всем датам логи
//        while ($helpDate < $endDate)
        while ($helpDate->lessThan($endDate))
        {
            $data[$i] = [
                'index' => $i,
                'category' => $helpDate->format('Y-m'),
                'income' => 0,
                'income_invest' => 0,
                'income_property' => 0,
                'plan_income' => 0,
                'income_diff' => 0,
                'property' => 0,
                'yellow_invest' => 0,
                'outcome' => 0,
                'outcome_invest' => 0,
                'outcome_property' => 0,
                'plan_outcome' => 0,
                'plan_outcome_invest' => 0,
                'outcome_diff' => 0,
                'grey_undefined' => 0,
                'debug' => []
            ];

            $income = 0;
            $planIncome = 0;

            $typeIds = $typeIds ?: [];

            if($typeIds)
            {
                foreach ($typeIds as $typeId)
                {
                    switch ($typeId)
                    {
                        case CreditLog::SALARY:
                            $income += $this->getTransactionsChartIncome($helpDate->copy(), $currency, $accountIds);
                            $planIncome += $this->getTransactionsChartPlanIncome($helpDate->copy(), $currency, $accountIds);

                            break;
                        case CreditLog::BROKER:
                            $income += $this->getTransactionsChartIncomeInvest($helpDate->copy(), $currency, $accountIds);
                            break;
                        case CreditLog::RENT:
                            $income += $this->getTransactionsChartRentIncome($helpDate->copy(), $currency, $accountIds);
                            break;
                        case CreditLog::SELL_PROPERTY:
                            $income += $this->getTransactionsChartIncomeProperty($helpDate->copy(), $currency, $accountIds);
                            break;
                    }
                }
            }

            $outcome = $this->getTransactionsChartOutcome($helpDate->copy(), $currency, $accountIds);
            $outcomeInvest = $this->getTransactionsChartOutcomeInvest($helpDate->copy(), $currency, $accountIds);
            $outcomeProperty = $this->getTransactionsChartOutcomeProperty($helpDate->copy(), $currency, $accountIds);

            $planOutcome = $this->getTransactionsChartPlanOutcome($helpDate->copy(), $currency, $accountIds);
            $planOutcomeInvest = $this->getTransactionsChartPlanOutcomeInvest($helpDate->copy(), $currency, $accountIds);

            $undefined  = 0;

            $data[$i]['income'] = $income;
            $data[$i]['plan_income'] = $planIncome;
            $data[$i]['outcome'] = abs($outcome);
            $data[$i]['outcome_invest'] = abs($outcomeInvest);
            $data[$i]['outcome_property'] = abs($outcomeProperty);
            $data[$i]['plan_outcome'] = abs($planOutcome);
            $data[$i]['plan_outcome_invest'] = abs($planOutcomeInvest);

            $helpDate->addMonth();
            $i++;
        }

        $sumIncome = array_sum(array_column($data, 'income'));
        $sumPlanIncome = array_sum(array_column($data, 'plan_income'));
        $sumOutcome = array_sum(array_column($data, 'outcome'));
        $sumOutcomeInvest = array_sum(array_column($data, 'outcome_invest'));
        $sumOutcomeProperty = array_sum(array_column($data, 'outcome_property'));
        $sumPlanOutcome = array_sum(array_column($data, 'plan_outcome'));
        $sumPlanOutcomeInvest = array_sum(array_column($data, 'plan_outcome_invest'));


        $diff = (abs($sumOutcome) + abs($sumOutcomeInvest) + abs($sumOutcomeProperty)) - $sumIncome;
        $sumUndefined  = $diff > 0 ? $diff : 0;


        $res = [];
        $res[0]['index'] = 0;
        $res[0]['category'] = $startDate->format('Y');
        $res[0]['income'] = $sumIncome;
        $res[0]['plan_income'] = $sumPlanIncome;
        $res[0]['outcome'] = $sumOutcome;
        $res[0]['outcome_invest'] = $sumOutcomeInvest;
        $res[0]['outcome_property'] = $sumOutcomeProperty;
        $res[0]['plan_outcome'] = $sumPlanOutcome;
        $res[0]['plan_outcome_invest'] = $sumPlanOutcomeInvest;

        $diffIncome = $sumPlanIncome - $sumIncome;
        $res[0]['income_diff'] = $diffIncome > 0 ? $diffIncome : 0;

        $diffOutcome = $sumPlanOutcome - $sumOutcome;
        $res[0]['outcome_diff'] = $diffOutcome > 0 ? $diffOutcome : 0;

        return $res;
    }

    /**
     * @param $nowDate
     * @param Currency $currency
     * @param $accountIds
     * @return mixed
     */
    public function getTransactionsChartIncome($nowDate, Currency $currency, $accountIds)
    {
        $actives = Active::whereIntegerInRaw('type_id', array_merge(Active::SALARY_GROUP, [Active::CUSTOM_INCOME]))
            ->where('user_id', $this->id)
            ->get();

        $paymentIds = ActivePayment::getPaymentQuery($actives)->pluck('id');

        $sum = 0;
        $logsQuery = $this->logs()
            ->whereYear('paid_at', $nowDate->year)
            ->whereMonth('paid_at', $nowDate->month);

        if($accountIds)
        {
            $logsQuery->whereIntegerInRaw('account_id', $accountIds);
        }

        $logs = $logsQuery->where(function ($query) use ($paymentIds)
            {
                $query->where('item_type', 'active.payment')
                    ->whereIntegerInRaw('item_id', $paymentIds);
            })
            ->get();

        foreach ($logs as $log)
        {
            /**
             * @var CreditLog $log
             */
            $sum += $currency->convert($log->sum, $log->currency_id, $log->paid_at);
        }

        return $sum;
    }

    /**
     * @param $nowDate
     * @param Currency $currency
     * @param $accountIds
     * @return int
     */
    public function getTransactionsChartPlanIncome($nowDate, Currency $currency, $accountIds)
    {
        $activeIds = Active::whereIntegerInRaw('type_id', array_merge(Active::SALARY_GROUP, [Active::CUSTOM_INCOME]))
            ->where('user_id', $this->id)
            ->pluck('id');

        /**
         * @var Active[] $actives
         */
        $actives = Active::whereIntegerInRaw('id', $activeIds)
            ->get();


        $planIncome = 0;
        foreach ($actives as $active)
        {
            $planIncome += $active->getTransactionsPlanIncome($nowDate, $currency);
        }

        return $planIncome;
    }

    /**
     * @param $nowDate
     * @param Currency $currency
     * @param $accountIds
     * @return mixed
     */
    public function getTransactionsChartIncomeInvest($nowDate, Currency $currency, $accountIds)
    {
        $brokerAccountIds = UserSubaccount::whereHas('user_account', function ($query)
        {
            $query->where('type_id', UserAccount::BROKER_ACCOUNT)
                ->where('user_id', $this->id);
        })
            ->pluck('id');

        $anotherAccountIds = UserSubaccount::whereHas('user_account', function ($query)
        {
            $query->where('type_id', '!=', UserAccount::BROKER_ACCOUNT)
                ->where('user_id', $this->id);
        })
            ->pluck('id');

        $transferIds = Transfer::whereIntegerInRaw('from_account_id', $brokerAccountIds)
            ->whereIntegerInRaw('to_account_id', $anotherAccountIds)
            ->where('user_id', $this->id)
            ->pluck('id');

        $sum = 0;
        $logsQuery = $this->logs()
            ->whereYear('paid_at', $nowDate->year)
            ->whereMonth('paid_at', $nowDate->month);

        if($accountIds)
        {
            $logsQuery->whereIntegerInRaw('account_id', $accountIds);
        }

        $logs = $logsQuery->where(function ($query) use ($transferIds)
            {
                $query->where('item_type', 'transfer')
                    ->whereIntegerInRaw('item_id', $transferIds)
                    ->where('sum', '>', 0);
            })
            ->get();

        foreach ($logs as $log)
        {
            /**
             * @var CreditLog $log
             */
            $sum += $currency->convert($log->sum, $log->currency_id, $log->paid_at);
        }

        return $sum;
    }

    /**
     * @param $nowDate
     * @param Currency $currency
     * @param $accountIds
     * @return mixed
     */
    public function getTransactionsChartRentIncome($nowDate, Currency $currency, $accountIds)
    {
        $actives = Active::whereIntegerInRaw('type_id', array_merge(Active::PROPERTY_GROUP, [Active::CUSTOM_PROPERTY]))
            ->where('user_id', $this->id)
            ->get();

        $paymentIds = ActivePayment::getPaymentQuery($actives)->pluck('id');

        $sum = 0;
        $logsQuery = $this->logs()
            ->whereYear('paid_at', $nowDate->year)
            ->whereMonth('paid_at', $nowDate->month);

        if($accountIds)
        {
            $logsQuery->whereIntegerInRaw('account_id', $accountIds);
        }

        $logs = $logsQuery->where(function ($query) use ($paymentIds)
            {
                $query->where('item_type', 'active.payment')
                    ->whereIntegerInRaw('item_id', $paymentIds);
            })
            ->where('sum', '>', 0)
            ->get();

        foreach ($logs as $log)
        {
            /**
             * @var CreditLog $log
             */
            $sum += $currency->convert($log->sum, $log->currency_id, $log->paid_at);
        }

        return $sum;
    }

    /**
     * @param $nowDate
     * @param Currency $currency
     * @param $accountIds
     * @return int
     */
    public function getTransactionsChartIncomeProperty($nowDate, Currency $currency, $accountIds)
    {
        $activesQuery = Active::with('logs')
            ->whereYear('sell_at', $nowDate->year)
            ->whereMonth('sell_at', $nowDate->month)
            ->whereIntegerInRaw('type_id', array_merge(Active::PROPERTY_GROUP, [Active::CUSTOM_PROPERTY]))
            ->where('user_id', $this->id);

        if($accountIds)
        {
            $activesQuery->whereIntegerInRaw('sell_account_id', $accountIds);
        }

        $actives = $activesQuery->get();

        $sum = 0;
        foreach ($actives as $active)
        {
            $sum += $currency->convert($active->sell_sum, $active->sell_currency_id, $active->sell_at);
        }

        return $sum;
    }

    /**
     * @param $nowDate
     * @param Currency $currency
     * @param $accountIds
     * @return int
     */
    public function getTransactionsChartOutcome($nowDate, Currency $currency, $accountIds)
    {
        $sum = 0;
        $logsQuery = $this
            ->logs()
            ->whereYear('paid_at', $nowDate->year)
            ->whereMonth('paid_at', $nowDate->month)
            ->where(function($query){
                $query->where('item_type', 'active.payment');
            });

        if($accountIds)
        {
            $logsQuery->whereIntegerInRaw('account_id', $accountIds);
        }

        $logs = $logsQuery->where('sum', '<', 0)
            ->get();


        foreach ($logs as $log)
        {
            $sum += $currency->convert($log->sum, $log->currency_id, $log->paid_at);
        }

        return $sum;
    }

    /**
     * @param $nowDate
     * @param Currency $currency
     * @param $accountIds
     * @return int
     */
    public function getTransactionsChartOutcomeInvest($nowDate, Currency $currency, $accountIds)
    {
        $brokerAccountIds = UserSubaccount::whereHas('user_account', function ($query)
        {
            $query->where('type_id', UserAccount::BROKER_ACCOUNT)
                ->where('user_id', $this->id);
        })
            ->pluck('id');

        $anotherAccountIds = UserSubaccount::whereHas('user_account', function ($query)
        {
            $query->where('type_id', '!=', UserAccount::BROKER_ACCOUNT)
                ->where('user_id', $this->id);
        })
            ->pluck('id');

        $transferIds = Transfer::whereIntegerInRaw('from_account_id', $anotherAccountIds)
            ->whereIntegerInRaw('to_account_id', $brokerAccountIds)
            ->where('user_id', $this->id)
            ->pluck('id');

        $sum = 0;
        $logsQuery = $this->logs()
            ->whereYear('paid_at', $nowDate->year)
            ->whereMonth('paid_at', $nowDate->month);

        if($accountIds)
        {
            $logsQuery->whereIntegerInRaw('account_id', $accountIds);
        }

        $logs = $logsQuery->where(function ($query) use ($transferIds)
            {
                $query->where('item_type', 'transfer')
                    ->whereIntegerInRaw('item_id', $transferIds)
                    ->where('sum', '>', 0);
            })
            ->get();

        foreach ($logs as $log)
        {
            /**
             * @var CreditLog $log
             */
            $sum += $currency->convert($log->sum, $log->currency_id, $log->paid_at);
        }

        return $sum;
    }

    /**
     * @param $nowDate
     * @param Currency $currency
     * @param $accountIds
     * @return int
     */
    public function getTransactionsChartOutcomeProperty($nowDate, Currency $currency, $accountIds)
    {
        $sum = 0;
        $actives = Active::whereIntegerInRaw('type_id', array_merge(Active::PROPERTY_GROUP, [Active::CUSTOM_PROPERTY]))
            ->whereYear('buy_at', $nowDate->year)
            ->whereMonth('buy_at', $nowDate->month)
            ->where('user_id', $this->id)
            ->where('action_id', Active::BUY)
            ->get();

        /**
         * @var Active[] $actives
         */
        foreach ($actives as $active)
        {
            $sum += $currency->convert($active->buy_sum, $active->buy_currency_id, $active->buy_at);
        }

        return $sum;
    }

    /**
     * @param $nowDate
     * @param Currency $currency
     * @param $accountIds
     * @return int
     */
    public function getTransactionsChartPlanOutcome($nowDate, Currency $currency, $accountIds)
    {
        $sum = 0;

        $actives = Active::whereIntegerInRaw('type_id', array_merge(Active::SPEND_OBLIGATION_GROUP, [Active::CUSTOM_OBLIGATION]))
            ->where('user_id', $this->id)
            ->get();

        /**
         * @var Active[] $actives
         */
        foreach ($actives as $active)
        {
            $sum += $active->getTransactionsOutcome($nowDate, $currency);
        }

        $activeIds = Active::whereIntegerInRaw('type_id', array_merge(Active::SALARY_GROUP, [Active::CUSTOM_INCOME]))
            ->where('user_id', $this->id)
            ->pluck('id');

        /**
         * @var Active[] $actives
         */
        $actives = Active::whereIntegerInRaw('id', $activeIds)
            ->get();


        foreach ($actives as $active)
        {
            $sum += $active->getTransactionsPlanOutcome($nowDate, $currency);
        }

        return $sum;
    }

    /**
     * @param $nowDate
     * @param Currency $currency
     * @param $accountIds
     * @return int
     */
    public function getTransactionsChartPlanOutcomeInvest($nowDate, Currency $currency, $accountIds)
    {
        $sum = 0;

        $payments = ActiveGoalPayment::whereHas('goal', function ($query)
            {
                $query->where('user_id', $this->id);
            })
            ->whereYear('paid_at', $nowDate->year)
            ->whereMonth('paid_at', $nowDate->month)
            ->get();

        foreach ($payments as $payment)
        {
            /**
             * @var ActiveGoalPayment $payment
             */
            $sum += $currency->convert($payment->sum, $payment->currency_id, $payment->paid_at);
        }

        return $sum;
    }

//    public function getUndefined($nowDate, $currency, $accountIds)
//    {
//        $sum = 0;
//        $undefinedItems = $this
//            ->logs()
//            ->whereYear('paid_at', $nowDate->year)
//            ->whereMonth('paid_at', $nowDate->month)
//            ->where('item_type', 'transfer')
//            ->whereIntegerInRaw('account_id', $accountIds ? $accountIds : [])
//            ->where('credits_after', '<', 0)
//            ->get();
//
//        /**
//         * @var CreditLog $item
//         */
//        foreach ($undefinedItems as $item)
//        {
//            if($item->credits_before <= 0)
//            {
//                $sum += $item->sum;
//            }else if($item->credits_before > 0){
//                $sum += $item->before + $item->sum;
//            }
//        }
//
//        return $sum;
//    }


}