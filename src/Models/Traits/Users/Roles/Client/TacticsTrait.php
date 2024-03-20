<?php
namespace Common\Models\Traits\Users\Roles\Client;

use App\Models\Accounts\UserAccount;
use App\Models\Accounts\UserAccountCurrency;
use App\Models\Actives\Active;
use App\Models\Actives\ActiveGoalPayment;
use App\Models\Actives\ActiveIncomeExpensesMonth;
use App\Models\Actives\ActivePayment;
use App\Models\CreditLog;
use App\Models\Transfers\Transfer;
use Common\Models\Users\Roles\Client;
use Carbon\Carbon;
use Common\Models\Currency;
use Common\Models\Interfaces\Catalog\DefinitionActiveConst;
use Exception;
use Illuminate\Support\Collection;

/**
 * Trait TacticsTrait
 *
 * @mixin Client
 *
 * @package App\Models\Traits
 */
trait TacticsTrait
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
    public function getChartTacticsByYear($currency, Carbon $startDate, Carbon $endDate, $step, $accountIds, $typeIds)
    {
        if(empty($this->start_enter_at))
        {
            throw new Exception(__('model.tactics.chart_tactics_by_year.start_enter_at'));
        }

        $data = [];

        $i = 0;
        $nowDate = $startDate->copy();

        $this->addPoint(Carbon::now(), '#ff0000', __('model.tactics.chart_tactics_by_year.today'));
        $this->addPoint($this->start_enter_at, '#ff0000', __('model.tactics.chart_tactics_by_year.date_data'));

        $periods = [];
        $index = [];

        $planIncomeActives = $this->getTacticsChartPlanIncomeByYear();
        $planOutcomeActives = $this->getTacticsChartPlanOutcomeByYear();//review

        while ($nowDate->lessThan($endDate))
        {
            $planIncome = 0;
            $planOutcome = 0;

            $index[$nowDate->format('Y')] = $i;
            $data[$i] = [
                'index' => $i,
                'category' => $nowDate->format('Y'),
                'income' => 0,
                'income_additional' => 0,
                'income_main' => 0,
                'income_invest' => 0,
                'income_property' => 0,
                'plan_income' => 0,
                'income_diff' => 0,
                'property' => 0,
                'yellow_invest' => 0,
                'outcome' => 0,
                'base_outcome' => 0,
                'additional_outcome' => 0,
                'outcome_obligation' => 0,
                'outcome_invest' => 0,
                'outcome_property' => 0,
                'plan_outcome' => 0,
                'plan_outcome_invest' => 0,
                'outcome_diff' => 0,
                'grey_undefined' => 0,
                'debug' => []
            ];

            foreach ($planIncomeActives as $active)
            {
                $planIncome += $active->getTacticsPlanIncomeByYear($nowDate, $currency);
            }
            foreach ($planOutcomeActives as $active)
            {
                $planOutcome += $active->getTacticsPlanOutcomeByYear($nowDate, $currency);
            }

            $data[$i]['plan_income'] = $planIncome;
            $data[$i]['plan_outcome'] = abs($planOutcome);

            $periods[] = $nowDate->copy();

            $nowDate->addYear();
            $i++;
        }

        $callback = function ($field, $query) use ($periods)
        {
            $query->where(function ($query) use ($field, $periods)
            {
                foreach ($periods as $k => $nowDate)
                {
                    if($k === 0){
                        $query->where(function ($query) use ($field, $nowDate)
                        {
                            $query->whereYear($field, $nowDate->year);
                        });
                    }else{
                        $query->orWhere(function ($query) use ($field, $nowDate)
                        {
                            $query->whereYear($field, $nowDate->year);
                        });
                    }
                }
            });
        };

        $callbackKey = function ($field, &$data, $index, $currency, $log)
        {
            $data[$index[$log->paid_at->format('Y')]][$field] += $currency->convert($log->sum, $log->currency_id, $log->paid_at);
        };

        $callbackKeyActiveBuy = function ($field, &$data, $index, $currency, $active)
        {
            $data[$index[$active->buy_at->format('Y')]][$field] += $currency->convert($active->buy_sum, $active->buy_currency_id, $active->buy_at);
        };

        $callbackKeyActiveSell = function ($field, &$data, $index, $currency, $active)
        {
            $data[$index[$active->sell_at->format('Y')]][$field] += $currency->convert($active->sell_sum, $active->sell_currency_id, $active->sell_at);
        };


        $this->getTacticsChartIncome($callback, $callbackKey, $data, $index, 'income', $currency, $accountIds);
        $this->getTacticsChartIncomeAdditional($callback, $callbackKey, $data, $index, 'income_additional', $currency, $accountIds);
        $this->getTacticsChartIncomeMain($callback, $callbackKey, $data, $index, 'income_main', $currency, $accountIds);

//        $this->getTacticsChartIncomeByOwnActive($callback, $callbackKeyActiveSell, $data, $index, 'income', $currency, $accountIds);
        $this->getTacticsChartOutcome($callback, $callbackKey, $data, $index, 'outcome', $currency, $accountIds);
//        $this->getTacticsChartOutcomeByOwnActive($callback, $callbackKeyActiveBuy, $data, $index, 'outcome', $currency, $accountIds);
//        $this->getTacticsChartOutcomeInvest($callback, $callbackKey, $data, $index, 'outcome_invest', $currency, $accountIds);
//        $this->getTacticsChartOutcomeProperty($callback, $callbackKeyActiveBuy, $data, $index, 'outcome_property', $currency, $accountIds);
//        $this->getTacticsChartPlanOutcomeInvest($callback, $callbackKey, $data, $index, 'plan_outcome_invest', $currency, $accountIds);

        $this->getTacticsChartBaseOutcome($callback, $callbackKey, $data, $index, 'base_outcome', $currency, $accountIds);
        $this->getTacticsChartAdditionalOutcome($callback, $callbackKey, $data, $index, 'additional_outcome', $currency, $accountIds);
        $this->getTacticsChartObligationOutcome($callback, $callbackKey, $data, $index, 'outcome_obligation', $currency, $accountIds);

        foreach ($data as $i => &$item)
        {
            $diff = (abs($data[$i]['outcome']) + abs($data[$i]['outcome_invest']) + abs($data[$i]['outcome_property'])) - $data[$i]['income'];

            $data[$i]['outcome'] = abs($data[$i]['outcome']);
            $data[$i]['base_outcome'] = abs($data[$i]['base_outcome']);
            $data[$i]['additional_outcome'] = abs($data[$i]['additional_outcome']);
            $data[$i]['outcome_obligation'] = abs($data[$i]['outcome_obligation']);
            $data[$i]['outcome_invest'] = abs($data[$i]['outcome_invest']);
            $data[$i]['outcome_property'] = abs($data[$i]['outcome_property']);
            $data[$i]['plan_outcome'] = abs($data[$i]['plan_outcome']);
            $data[$i]['plan_outcome_invest'] = abs($data[$i]['plan_outcome_invest']);

            $diffIncome = $data[$i]['plan_income'] - $data[$i]['income'];
            $data[$i]['income_diff'] = $diffIncome > 0 ? $diffIncome : 0;

            $diffOutcome = abs($data[$i]['plan_outcome']) - abs($data[$i]['outcome']);
            $data[$i]['outcome_diff'] = $diffOutcome > 0 ? $diffOutcome : 0;
        }

        return $data;
    }

    /**
     * @param $currency
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param $accountIds
     * @param $typeIds
     * @return array
     * @throws Exception
     */
    public function getChartTacticsByMonth($currency, Carbon $startDate, Carbon $endDate, $accountIds, $typeIds)
    {
        if(empty($this->start_enter_at))
        {
            throw new Exception(__('model.tactics.chart_tactics_by_year.start_enter_at'));
        }

        $data = [];

        $i = 0;
        $nowDate = $startDate->copy();

        $this->addPoint(Carbon::now(), '#ff0000', __('model.tactics.chart_tactics_by_year.today'));
        $this->addPoint($this->start_enter_at, '#ff0000', __('model.tactics.chart_tactics_by_year.date_data'));

        $planIncomeActivesMonths = $this->getTacticsChartPlanIncomeByMonth();


        $periods = [];
        $index = [];
        //TODO оптимизировать запрос, сразу запросить по всем датам логи
        while ($nowDate->lessThan($endDate))
        {
            $planIncome = 0;
            $planOutcome = 0;

            $index[$nowDate->format('Y-m')] = $i;

            $data[$i] = [
                'index' => $i,
                'category' => $nowDate->format('Y-m'),
                'income' => 0,
                'income_additional' => 0,
                'income_main' => 0,
                'income_invest' => 0,
                'income_property' => 0,
                'plan_income' => 0,
                'income_diff' => 0,
                'property' => 0,
                'yellow_invest' => 0,
                'outcome' => 0,
                'base_outcome' => 0,
                'additional_outcome' => 0,
                'outcome_obligation' => 0,
                'outcome_invest' => 0,
                'outcome_property' => 0,
                'plan_outcome' => 0,
                'plan_outcome_invest' => 0,
                'outcome_diff' => 0,
                'grey_undefined' => 0,
                'debug' => []
            ];

            if(isset($planIncomeActivesMonths[$nowDate->format('Y-m')]))
            {
                $planIncome += $planIncomeActivesMonths[$nowDate->format('Y-m')]->income;
                $planOutcome += $planIncomeActivesMonths[$nowDate->format('Y-m')]->outcome;
            }

            $data[$i]['plan_income'] = $planIncome;
            $data[$i]['plan_outcome'] = abs($planOutcome);

            $periods[] = $nowDate->copy();
            $nowDate->addMonth();
            $i++;
        }

        $callback = function ($field, $query) use ($periods)
        {
            //TODO можно упростить запрос на BETWEEN
            $query->where(function ($query) use ($field, $periods)
            {
                foreach ($periods as $k => $nowDate)
                {
                    if($k === 0){
                        $query->where(function ($query) use ($field, $nowDate)
                        {
                            $query->whereYear($field, $nowDate->year)
                                ->whereMonth($field, $nowDate->month);
                        });
                    }else{
                        $query->orWhere(function ($query) use ($field, $nowDate)
                        {
                            $query->whereYear($field, $nowDate->year)
                                ->whereMonth($field, $nowDate->month);
                        });
                    }
                }
            });
        };

        /**
         * @param $field
         * @param $data
         * @param $index
         * @param Currency $currency
         * @param $log
         */
        $callbackKey = function ($field, &$data, $index, $currency, $log)
        {
            $data[$index[$log->paid_at->format('Y-m')]][$field] += abs($currency->convert($log->sum, $log->currency_id, $log->paid_at));
        };

        /**
         * @param $field
         * @param $data
         * @param $index
         * @param Currency $currency
         * @param $active
         */
        $callbackKeyActiveBuy = function ($field, &$data, $index, $currency, $active)
        {
            $data[$index[$active->buy_at->format('Y-m')]][$field] += abs($currency->convert($active->buy_sum, $active->buy_currency_id, $active->buy_at));
        };

        /**
         * @param $field
         * @param $data
         * @param $index
         * @param Currency $currency
         * @param $active
         */
        $callbackKeyActiveSell = function ($field, &$data, $index, $currency, $active)
        {
            $data[$index[$active->sell_at->format('Y-m')]][$field] += abs($currency->convert($active->sell_sum, $active->sell_currency_id, $active->sell_at));
        };

        $this->getTacticsChartIncome($callback, $callbackKey, $data, $index, 'income', $currency, $accountIds);
        $this->getTacticsChartIncomeAdditional($callback, $callbackKey, $data, $index, 'income_additional', $currency, $accountIds);
        $this->getTacticsChartIncomeMain($callback, $callbackKey, $data, $index, 'income_main', $currency, $accountIds);
//        $this->getTacticsChartIncomeByOwnActive($callback, $callbackKeyActiveSell, $data, $index, 'income', $currency, $accountIds);

        $this->getTacticsChartOutcome($callback, $callbackKey, $data, $index, 'outcome', $currency, $accountIds);
        $this->getTacticsChartBaseOutcome($callback, $callbackKey, $data, $index, 'base_outcome', $currency, $accountIds);

//        $this->getTacticsChartOutcomeByOwnActive($callback, $callbackKeyActiveBuy, $data, $index, 'outcome', $currency, $accountIds);
//        $this->getTacticsChartOutcomeInvest($callback, $callbackKey, $data, $index, 'outcome_invest', $currency, $accountIds);
//        $this->getTacticsChartOutcomeProperty($callback, $callbackKeyActiveBuy, $data, $index, 'outcome_property', $currency, $accountIds);

        $this->getTacticsChartPlanOutcomeInvest($callback, $callbackKey, $data, $index, 'plan_outcome_invest', $currency, $accountIds);
        $this->getTacticsChartObligationOutcome($callback, $callbackKey, $data, $index, 'outcome_obligation', $currency, $accountIds);

        foreach ($data as $i => &$item)
        {
            $diff = (abs($data[$i]['outcome']) + abs($data[$i]['outcome_invest']) + abs($data[$i]['outcome_property'])) - $data[$i]['income'];
//            $undefined  = $diff > 0 ? $diff : 0;

            $data[$i]['outcome'] = abs($data[$i]['outcome']);
            $data[$i]['base_outcome'] = abs($data[$i]['base_outcome']);
            $data[$i]['additional_outcome'] = abs($data[$i]['additional_outcome']);
            $data[$i]['outcome_obligation'] = abs($data[$i]['outcome_obligation']);
            $data[$i]['outcome_invest'] = abs($data[$i]['outcome_invest']);
            $data[$i]['outcome_property'] = abs($data[$i]['outcome_property']);
            $data[$i]['plan_outcome'] = abs($data[$i]['plan_outcome']);
            $data[$i]['plan_outcome_invest'] = abs($data[$i]['plan_outcome_invest']);

            $diffIncome = $data[$i]['plan_income'] - $data[$i]['income'];
            $data[$i]['income_diff'] = $diffIncome > 0 ? $diffIncome : 0;

            $diffOutcome = abs($data[$i]['plan_outcome']) - abs($data[$i]['outcome']);
            $data[$i]['outcome_diff'] = $diffOutcome > 0 ? $diffOutcome : 0;
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
    public function getChartTotalTactics($currency, Carbon $startDate, Carbon $endDate, $step, $accountIds, $typeIds)
    {

    }

    /**
     * @param $callback
     * @param $callbackKey
     * @param $data
     * @param $index
     * @param $field
     * @param Currency $currency
     * @param $accountIds
     */
    public function getTacticsChartIncome($callback, $callbackKey, &$data, $index, $field, Currency $currency, $accountIds)
    {
        $actives = Active::whereIntegerInRaw('type_id', array_merge(DefinitionActiveConst::INCOME_GROUP, [DefinitionActiveConst::CUSTOM_INCOME]))
            ->where('user_id', $this->id)
            ->get();

        $paymentIds = ActivePayment::getPaymentQuery($actives)
            ->pluck('id');

        $logsQuery = $this->logs()
            ->whereHas('account', function ($query){
                $query->where('is_visible', 1);
            });

        $callback('paid_at', $logsQuery);

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
            $callbackKey($field, $data, $index, $currency, $log);
        }
    }

    /**
     * @param $callback
     * @param $callbackKey
     * @param $data
     * @param $index
     * @param $field
     * @param Currency $currency
     * @param $accountIds
     */
    public function getTacticsChartIncomeAdditional($callback, $callbackKey, &$data, $index, $field, Currency $currency, $accountIds)
    {
        $actives = Active::whereIntegerInRaw('type_id', array_merge(DefinitionActiveConst::INCOME_GROUP, [DefinitionActiveConst::CUSTOM_INCOME]))
            ->where(function ($query){
                $query->where('is_main_income', 0)
                    ->orWhereNull('is_main_income');
            })
            ->where('user_id', $this->id)
            ->get();

        $paymentIds = ActivePayment::getPaymentQuery($actives)
            ->pluck('id');

        $logsQuery = $this->logs()
            ->whereHas('account', function ($query){
                $query->where('is_visible', 1);
            });

        $callback('paid_at', $logsQuery);

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
            $callbackKey($field, $data, $index, $currency, $log);
        }
    }

    /**
     * @param $callback
     * @param $callbackKey
     * @param $data
     * @param $index
     * @param $field
     * @param Currency $currency
     * @param $accountIds
     */
    public function getTacticsChartIncomeMain($callback, $callbackKey, &$data, $index, $field, Currency $currency, $accountIds)
    {
        $actives = Active::whereIntegerInRaw('type_id', array_merge(DefinitionActiveConst::INCOME_GROUP, [DefinitionActiveConst::CUSTOM_INCOME]))
            ->where('is_main_income', 1)
            ->where('user_id', $this->id)
            ->get();

        $paymentIds = ActivePayment::getPaymentQuery($actives)
            ->pluck('id');

        $logsQuery = $this->logs()
            ->whereHas('account', function ($query){
                $query->where('is_visible', 1);
            });

        $callback('paid_at', $logsQuery);

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
            $callbackKey($field, $data, $index, $currency, $log);
        }
    }

    /**
     * @param $callback
     * @param $callbackKey
     * @param $data
     * @param $index
     * @param $field
     * @param Currency $currency
     * @param $accountIds
     */
    public function getTacticsChartIncomeByOwnActive($callback, $callbackKey, &$data, $index, $field, Currency $currency, $accountIds)
    {
        $activesQuery = Active::wherePropertyType()
            ->where('group_id', DefinitionActiveConst::OWN)
            ->whereNotNull('sell_at')
            ->where('user_id', $this->id);

        $callback('buy_at', $activesQuery);

        $actives = $activesQuery->get();

        foreach ($actives as $active)
        {
            $callbackKey($field, $data, $index, $currency, $active);
        }
    }

    /**
     * @return Collection
     */
    public function getTacticsChartPlanIncomeByMonth()
    {
        $activeIds = Active::whereIntegerInRaw('type_id', DefinitionActiveConst::SALARY_GROUP)
            ->where('user_id', $this->id)
            ->pluck('id');

        return ActiveIncomeExpensesMonth::whereIntegerInRaw('active_id', $activeIds)
            ->get()
            ->keyBy(function ($item){
                return $item->date->format('Y-m');
            });
    }

    /**
     * @return Active[]
     */
    public function getTacticsChartPlanIncomeByYear()
    {
        $activeIds = Active::whereIntegerInRaw('type_id', DefinitionActiveConst::SALARY_GROUP)
            ->where('user_id', $this->id)
            ->pluck('id');

        /**
         * @var Active[] $actives
         */
        $actives = Active::whereIntegerInRaw('id', $activeIds)
            ->with('user')
            ->get();

        return $actives;
    }

    /**
     * @param $nowDate
     * @param Currency $currency
     * @param $accountIds
     * @return float|int
     */
    public function getTacticsChartIncomeInvest($nowDate, Currency $currency, $accountIds)
    {
        $brokerAccountIds = UserAccountCurrency::whereHas('user_account', function ($query)
        {
            $query->where('type_id', UserAccount::BROKER_ACCOUNT)
                ->where('user_id', $this->id);
        })
            ->pluck('id');

        $anotherAccountIds = UserAccountCurrency::whereHas('user_account', function ($query)
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
            ->whereHas('account', function ($query){
                $query->where('is_visible', 1);
            })
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
     * @return float|int
     */
    public function getTacticsChartRentIncome($nowDate, Currency $currency, $accountIds)
    {
        $actives = Active::whereIntegerInRaw('type_id', array_merge(DefinitionActiveConst::PROPERTY_GROUP, [DefinitionActiveConst::CUSTOM_PROPERTY]))
            ->where('user_id', $this->id)
            ->get();

        $paymentIds = ActivePayment::getPaymentQuery($actives)->pluck('id');

        $sum = 0;
        $logsQuery = $this->logs()
            ->whereHas('account', function ($query){
                $query->where('is_visible', 1);
            })
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
    public function getTacticsChartIncomeProperty($nowDate, Currency $currency, $accountIds)
    {
        $activesQuery = Active::with('logs')
            ->whereYear('sell_at', $nowDate->year)
            ->whereMonth('sell_at', $nowDate->month)
            ->whereIntegerInRaw('type_id', array_merge(DefinitionActiveConst::PROPERTY_GROUP, [DefinitionActiveConst::CUSTOM_PROPERTY]))
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
     * @param $callback
     * @param $callbackKey
     * @param $data
     * @param $index
     * @param $field
     * @param Currency $currency
     * @param $accountIds
     */
    public function getTacticsChartOutcome($callback, $callbackKey, &$data, $index, $field, Currency $currency, $accountIds)
    {
        $logsQuery = $this
            ->logs()
            ->whereHas('account', function ($query){
                $query->where('is_visible', 1);
            })
            ->where(function($query){
                $query->where('item_type', 'active.payment');
            });

        $callback('paid_at', $logsQuery);

        if($accountIds)
        {
            $logsQuery->whereIntegerInRaw('account_id', $accountIds);
        }

        $logs = $logsQuery->where('sum', '<', 0)
            ->get();

        foreach ($logs as $log)
        {
            $callbackKey($field, $data, $index, $currency, $log);
        }
    }
    /**
     * @param $callback
     * @param $callbackKey
     * @param $data
     * @param $index
     * @param $field
     * @param Currency $currency
     * @param $accountIds
     */
    public function getTacticsChartOutcomeByOwnActive($callback, $callbackKey, &$data, $index, $field, Currency $currency, $accountIds)
    {
        $activesQuery = Active::wherePropertyType()
            ->where('group_id', DefinitionActiveConst::OWN)
            ->where('user_id', $this->id);

        $callback('buy_at', $activesQuery);

        $actives = $activesQuery->get();

        foreach ($actives as $active)
        {
            $callbackKey($field, $data, $index, $currency, $active);
        }
    }

    /**
     * @param $callback
     * @param $callbackKey
     * @param $data
     * @param $index
     * @param $field
     * @param Currency $currency
     * @param $accountIds
     * @return void
     */
    public function getTacticsChartBaseOutcome($callback, $callbackKey, &$data, $index, $field, Currency $currency, $accountIds)
    {
        $logsQuery = $this
            ->logs()
            ->with('item', 'item.active')
            ->whereHas('account', function ($query){
                $query->where('is_visible', 1);
            })
            ->where(function($query){
                $query->where('item_type', 'active.payment');
            });

        $callback('paid_at', $logsQuery);

        if($accountIds)
        {
            $logsQuery->whereIntegerInRaw('account_id', $accountIds);
        }

        $logs = $logsQuery->where('sum', '<', 0)
            ->get();


        foreach ($logs as $log)
        {
            if(isset($log->item->item, $log->item->active) && !in_array($log->item->active->type_id, array_merge(DefinitionActiveConst::SPEND_OBLIGATION_GROUP, DefinitionActiveConst::CREDIT_OBLIGATION_GROUP, [DefinitionActiveConst::CUSTOM_OBLIGATION])))
            {
                $callbackKey($field, $data, $index, $currency, $log);
            }
        }
    }


    /**
     * @param $callback
     * @param $callbackKey
     * @param $data
     * @param $index
     * @param $field
     * @param Currency $currency
     * @param $accountIds
     */
    public function getTacticsChartAdditionalOutcome($callback, $callbackKey, &$data, $index, $field, Currency $currency, $accountIds)
    {
        $logsQuery = $this
            ->logs()
            ->whereHas('account', function ($query){
                $query->where('is_visible', 1);
            })
            ->where(function($query){
                $query->where('item_type', 'active.payment');
            });

        $callback('paid_at', $logsQuery);

        if($accountIds)
        {
            $logsQuery->whereIntegerInRaw('account_id', $accountIds);
        }

        $logs = $logsQuery->where('sum', '<', 0)
            ->get();

        foreach ($logs as $log)
        {
            if(isset($log->item->active) && $log->item->active->is_regular_outcome === false)
            {
                $callbackKey($field, $data, $index, $currency, $log);
            }
        }
    }

    /**
     * @param $callback
     * @param $callbackKey
     * @param $data
     * @param $index
     * @param $field
     * @param Currency $currency
     * @param $accountIds
     */
    public function getTacticsChartObligationOutcome($callback, $callbackKey, &$data, $index, $field, Currency $currency, $accountIds)
    {
        $logsQuery = $this
            ->logs()
            ->whereHas('account', function ($query){
                $query->where('is_visible', 1);
            })
            ->where(function($query){
                $query->where('item_type', 'active.payment');
            });

        $callback('paid_at', $logsQuery);

        if($accountIds)
        {
            $logsQuery->whereIntegerInRaw('account_id', $accountIds);
        }

        $logs = $logsQuery->where('sum', '<', 0)
            ->get();

        foreach ($logs as $log)
        {
            if(isset($log->item->active) && in_array($log->item->active->type_id, array_merge(DefinitionActiveConst::SPEND_OBLIGATION_GROUP, DefinitionActiveConst::CREDIT_OBLIGATION_GROUP, [DefinitionActiveConst::CUSTOM_OBLIGATION])))
            {
                $callbackKey($field, $data, $index, $currency, $log);
            }
        }
    }

    /**
     * @param $callback
     * @param $callbackKey
     * @param $data
     * @param $index
     * @param $field
     * @param Currency $currency
     * @param $accountIds
     * @return int
     */
    public function getTacticsChartOutcomeInvest($callback, $callbackKey, &$data, $index, $field, Currency $currency, $accountIds)
    {
        $brokerAccountIds = UserAccountCurrency::whereHas('user_account', function ($query)
        {
            $query->where('type_id', UserAccount::BROKER_ACCOUNT)
                ->where('user_id', $this->id);
        })
            ->pluck('id');

        $anotherAccountIds = UserAccountCurrency::whereHas('user_account', function ($query)
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
            ->whereHas('account', function ($query){
                $query->where('is_visible', 1);
            });

        $callback('paid_at', $logsQuery);

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
            $callbackKey($field, $data, $index, $currency, $log);
        }

        return $sum;
    }

    /**
     * @param $callback
     * @param $callbackKey
     * @param $data
     * @param $index
     * @param $field
     * @param Currency $currency
     * @param $accountIds
     * @return float|int
     */
    public function getTacticsChartOutcomeProperty($callback, $callbackKey, &$data, $index, $field, Currency $currency, $accountIds)
    {
        $sum = 0;
        $activesQuery = Active::whereIntegerInRaw('type_id', array_merge(DefinitionActiveConst::PROPERTY_GROUP, [DefinitionActiveConst::CUSTOM_PROPERTY]))
            ->where('user_id', $this->id)
            ->where('action_id', DefinitionActiveConst::BUY);

        $callback('buy_at', $activesQuery);

        $actives = $activesQuery->get();

        /**
         * @var Active[] $actives
         */
        foreach ($actives as $active)
        {
            $callbackKey($field, $data, $index, $currency, $active);
        }

        return $sum;
    }

    /**
     * @return void
     */
    public function getTacticsChartPlanOutcomeByMonth()
    {
        $obligationIds = Active::whereIntegerInRaw('type_id', array_merge(Active::SPEND_OBLIGATION_GROUP, [Active::CUSTOM_OBLIGATION]))
            ->where('user_id', $this->id)
            ->get()
            ->pluck('id')
            ->toArray();

//        $activeIds = Active::whereIntegerInRaw('type_id', array_merge(Active::SALARY_GROUP, [Active::CUSTOM_INCOME]))
//            ->where('user_id', $this->id)
//            ->get()
//            ->pluck('id')
//            ->toArray();

        /**
         * @var Active[] $actives
         */
//        $actives = Active::whereIntegerInRaw('id', array_merge($activeIds, $obligationIds))
//            ->with('user')
//            ->get();




//        return $actives;
    }

    /**
     * @return Active[]
     */
    public function getTacticsChartPlanOutcomeByYear()
    {
        $obligationIds = Active::whereIntegerInRaw('type_id', array_merge(DefinitionActiveConst::SPEND_OBLIGATION_GROUP, [DefinitionActiveConst::CUSTOM_OBLIGATION]))
            ->where('user_id', $this->id)
            ->get()
            ->pluck('id')
            ->toArray();


        $activeIds = Active::whereIntegerInRaw('type_id', array_merge(DefinitionActiveConst::SALARY_GROUP, [DefinitionActiveConst::CUSTOM_INCOME]))
            ->where('user_id', $this->id)
            ->get()
            ->pluck('id')
            ->toArray();

        /**
         * @var Active[] $actives
         */
        $actives = Active::whereIntegerInRaw('id', array_merge($activeIds, $obligationIds))
            ->with('user')
            ->get();

        return $actives;
    }

    /**
     * @param $callback
     * @param $callbackKey
     * @param $data
     * @param $index
     * @param $field
     * @param Currency $currency
     * @param $accountIds
     * @return float|int
     */
    public function getTacticsChartPlanOutcomeInvest($callback, $callbackKey, &$data, $index, $field, Currency $currency, $accountIds)
    {
        $sum = 0;
        $paymentsQuery = ActiveGoalPayment::whereHas('goal', function ($query)
            {
                $query->where('user_id', $this->id);
            });

        $callback('paid_at', $paymentsQuery);

        $payments = $paymentsQuery->get();

        foreach ($payments as $payment)
        {
            /**
             * @var ActiveGoalPayment $payment
             */
            $callbackKey($field, $data, $index, $currency, $payment);
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