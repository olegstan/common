<?php
namespace Common\Models\Traits\Users\Roles;

use App\Models\Accounts\UserAccountCurrency;
use App\Models\Actives\Active;
use App\Models\Actives\ActiveGoal;
use App\Models\Actives\ActiveIncomeExpensesMonth;
use App\Models\Actives\Incomes\Salary;
use Common\Models\Users\User;
use Carbon\Carbon;
use DB;

/**
 * Trait UserPlanTrait
 *
 * @mixin User
 *
 * @package App\Models\Traits
 */
trait UserPlanTrait
{
    /**
     * @param Carbon $startDate
     * @param $salary
     */
    public function updatePlan(Carbon $startDate, $salary)
    {
        $data = [];
        $deletedData = [];

        if($startDate->isBefore($salary->buy_at))
        {
            $diffYears = $startDate->diffInYears($salary->buy_at);

            $startMonth = $this->career_start_month ?: 1;

            $helpDate = $startDate->copy()->month($startMonth)->startOfMonth();

            $account = UserAccountCurrency::generateMainTempAccount($this->id, $this->currency_id);

            for($n = 0; $n <= $diffYears; $n++)
            {
                $salaryFound = Salary::where('user_id', $this->id)
                    ->whereYear('buy_at', $helpDate->year)
                    ->first();

                if(!$salaryFound)
                {
                    /**
                     * @var Salary $salary
                     */
                    $salary = Salary::create([
                        'user_id' => $this->id,
                        'payment_type_id' => Active::PERIOD,
                        'income' => 0,
                        'income_neutral' => 0,
                        'income_negative' => 0,
                        'income_positive' => 0,
                        'additional_income' => 0,
                        'income_currency_id' => $this->currency_id,
                        'income_account_id' => $account->id,
                        'income_period_type_id' => Active::MONTHLY,
                        'outcome' => 0,
                        'additional_outcome' => 0,
                        'buy_at' => $helpDate->copy()->month($startMonth)->startOfMonth(),
                        'sell_at' => $helpDate->copy()->addYearNoOverflow()->month($startMonth)->startOfMonth(),
                        'tax' => 0
                    ]);

                    $salary->saveByMonths();

                    $data['date'][] = $salary->buy_at->format('Y');
                    $data['income_neutral'][] = $salary->income_neutral;
                    $data['income_negative'][] = $salary->income_negative;
                    $data['income_positive'][] = $salary->income_positive;
                    $data['outcome'][] = $salary->outcome;
                    $data['tax'][] = 0;
                }

                $helpDate->addYearNoOverflow();
            }
        }else{
            Salary::where('user_id', $this->id)
                ->whereYear('buy_at', '<', $startDate->year)
                ->chunkById(1000, function ($items) use (&$deletedData)
                {
                    foreach ($items as $item)
                    {
                        $deletedData[] = $item->buy_at->subYear()->format('Y');

                        $item->delete();
                    }
                });
        }

        $data['user_id'] = $this->id;
        $updatePlan['user'] = [
            'percent_positive' => $this->percent_positive,
            'percent_neutral' => $this->percent_neutral,
            'percent_negative' => $this->percent_negative,
            'index_positive_income' => $this->index_positive_income,
            'index_neutral_income' => $this->index_neutral_income,
            'index_negative_income' => $this->index_negative_income,
            'index_outcome' => $this->index_outcome,
            'start_enter_at' => $this->start_enter_at,
            'retired_age' => $this->retired_age,
            'dead_age' => $this->dead_age,
            'career_start_month' => $this->career_start_month,
        ];
        if ($data) {
            $updatePlan['plan'] = $data;
        }
        if ($deletedData) {
            $updatePlan['deleted_plan'] = $deletedData;
        }


        DB::table('user_update_plans')
            ->insert([
                'created_at' => Carbon::now(),
                'user_id' => $this->id,
                'data' => json_encode($updatePlan),
                'type_id' => ActiveGoal::CRT,
            ]);
    }


    /**
     * @param $currency
     * @return void
     */
    public function recalculateCurrencyPlan($currency)
    {
        //TODO сохранить прошлые значения в user_update_plans

        $account = UserAccountCurrency::generateMainTempAccount($this->id, $this->currency_id);

        $salaries = Salary::where('user_id', $this->id)
            ->get();

        //TODO сделать блокировку если идёт апдейт по планированию, чтобы цифры не перетирали друг друга
        foreach ($salaries as $salary)
        {
            //пересчитываем только если валюта отличается
            if($salary->income_currency_id !== $this->currency_id)
            {
                $salary->update([
                    'income_currency_id' => $this->currency_id,
                    'income_account_id' => $account->id,
                    'income_neutral' => $currency->convert($salary->income_neutral, $salary->income_currency_id, $salary->buy_at->copy()),
                    'income_negative' => $currency->convert($salary->income_negative, $salary->income_currency_id, $salary->buy_at->copy()),
                    'income_positive' => $currency->convert($salary->income_positive, $salary->income_currency_id, $salary->buy_at->copy()),
                    'additional_income' => $currency->convert($salary->additional_income, $salary->income_currency_id, $salary->buy_at->copy()),
                    'outcome' => $currency->convert($salary->outcome, $salary->income_currency_id, $salary->buy_at->copy()),
                    'additional_outcome' => $currency->convert($salary->additional_outcome, $salary->income_currency_id, $salary->buy_at->copy()),
                ]);
            }
        }
    }

    /**
     * @param Carbon $startDate
     */
    public function createPlan(Carbon $startDate): void
    {
        $data = [];
        $deletedData = [];

        //проверяем что данные заполнены
        if($this->start_enter_at && $this->birth_at)
        {
            $birthDate = $this->birth_at->startOfDay();
            $retiredDate = $birthDate->copy()->addYearsNoOverflow($this->retired_age)->endOfDay();
            $deadDate = $retiredDate->copy()->addYearsNoOverflow($this->dead_age + 1)->endOfDay();
            $startMonth = $this->career_start_month ?: 1;

            //даты для удаления лишних
            $ages = [];

            //проверяем что карьера и пенсия после даты внесения иначе график не построить
            if ($startDate->isBefore($deadDate))
            {
                $diffYears = $startDate->diffInYears($deadDate);
                $helpDate = $startDate->copy()->month($startMonth)->startOfMonth();

                $account = UserAccountCurrency::generateMainTempAccount($this->id, $this->currency_id);

                $firstSalary = Salary::where('user_id', $this->id)
                    ->orderBy('buy_at')
                    ->first();

                if ($firstSalary) {
                    $incomeNeutral = $firstSalary->income_neutral;
                    $incomeNegative = $firstSalary->income_negative;
                    $incomePositive = $firstSalary->income_positive;
                    $outcome = $firstSalary->outcome;
                    $tax = $firstSalary->tax;
                } else {
                    $incomeNeutral = 0;
                    $incomeNegative = 0;
                    $incomePositive = 0;
                    $outcome = 0;
                    $tax = 0;
                }

                $indexNeutral = $this->index_neutral_income;
                $indexNegative = $this->index_negative_income;
                $indexPositive = $this->index_positive_income;
                $indexOutcome = $this->index_outcome;

                for ($n = 0; $n < $diffYears; $n++)
                {
                    $ages[] = $helpDate->format('Y-m-d H:i:s');

                    $salary = Salary::where('user_id', $this->id)
                        ->where('buy_at', $helpDate)
                        ->first();

                    if(Salary::isRetire($this, $helpDate))
                    {
                        if($salary)
                        {
                            $incomeNeutral = $salary->income_neutral;
                            $incomeNegative = $salary->income_negative;
                            $incomePositive = $salary->income_positive;

                            if ($salary->outcome_changed) {
                                $outcome = $salary->outcome;
                            } else {
                                $outcome = floor($outcome + ($outcome / 100 * $indexOutcome));
                            }

                            $tax = $salary->tax;
                        }else{
                            $incomeNeutral = 0;
                            $incomeNegative = 0;
                            $incomePositive = 0;
                            $outcome = 0;
                            $tax = 0;
                        }
                    }elseif ($n !== 0)
                    {
                        if ($salary && $salary->income_neutral_changed) {
                            $incomeNeutral = $salary->income_neutral;
                        } else {
                            $incomeNeutral = floor($incomeNeutral + ($incomeNeutral / 100 * $indexNeutral));
                        }

                        if ($salary && $salary->income_negative_changed) {
                            $incomeNegative = $salary->income_negative;
                        } else {
                            $incomeNegative = floor($incomeNegative + ($incomeNegative / 100 * $indexNegative));
                        }

                        if ($salary && $salary->income_positive_changed) {
                            $incomePositive = $salary->income_positive;
                        } else {
                            $incomePositive = floor($incomePositive + ($incomePositive / 100 * $indexPositive));
                        }

                        if ($salary && $salary->outcome_changed) {
                            $outcome = $salary->outcome;
                        } else {
                            $outcome = floor($outcome + ($outcome / 100 * $indexOutcome));
                        }

                        if ($tax && $salary->tax_changed) {
                            $tax = $salary->tax;
                        }
                    }



                    if (!$salary) {
                        $salary = Salary::create([
                            'user_id' => $this->id,
                            'payment_type_id' => Active::PERIOD,
                            'income' => 0,
                            'income_neutral' => $incomeNeutral,
                            'income_negative' => $incomeNegative,
                            'income_positive' => $incomeNegative,
                            'additional_income' => 0,
                            'income_currency_id' => $this->currency_id,
                            'income_account_id' => $account->id,
                            'income_period_type_id' => Active::MONTHLY,
                            'outcome' => $outcome,
                            'additional_outcome' => 0,
                            'buy_at' => $helpDate->copy()->month($startMonth)->startOfMonth(),
                            'sell_at' => $helpDate->copy()->addYearNoOverflow()->month($startMonth)->startOfMonth(),
                            'tax' => $tax
                        ]);

                        $salary->saveByMonths();

                        $data['date'][] = $salary->buy_at->format('Y');
                        $data['income_neutral'][] = $salary->income_neutral;
                        $data['income_negative'][] = $salary->income_negative;
                        $data['income_positive'][] = $salary->income_positive;
                        $data['additional_income'][] = $salary->additional_income;
                        $data['outcome'][] = $salary->outcome;
                        $data['additional_outcome'][] = $salary->additional_outcome;
                        $data['tax'][] = $salary->tax;
                    } else {
                        $data['date'][] = $salary->buy_at->format('Y');
                        $data['income_neutral'][] = $salary->income_neutral;
                        $data['income_negative'][] = $salary->income_negative;
                        $data['income_positive'][] = $salary->income_positive;
                        $data['additional_income'][] = $salary->additional_income;
                        $data['outcome'][] = $salary->outcome;
                        $data['additional_outcome'][] = $salary->additional_outcome;
                        $data['tax'][] = $salary->tax;

                        $oldIncomeNeutral = $salary->income_neutral;
                        $oldIncomeNegative = $salary->income_negative;
                        $oldIncomePositive = $salary->income_positive;
                        $oldOutcome = $salary->outcome;

                        $salary->update([
                            'income_neutral' => $incomeNeutral,
                            'income_negative' => $incomeNegative,
                            'income_positive' => $incomePositive,
                            'outcome' => $outcome,
                            'buy_at' => $helpDate->copy()->month($startMonth)->startOfMonth(),
                            'sell_at' => $helpDate->copy()->addYearNoOverflow()->month($startMonth)->startOfMonth(),
                        ]);

                        if ($oldIncomeNeutral != $incomeNeutral || $oldIncomeNegative != $incomeNegative || $oldIncomePositive != $incomePositive || $oldOutcome != $outcome) {
                            ActiveIncomeExpensesMonth::where('user_id', $this->id)
                                ->where('active_id', $salary->id)
                                ->chunkById(1000, function ($items) {
                                    foreach ($items as $item) {
                                        $item->delete();
                                    }
                                });

                            $salary->saveByMonths();
                        }
                    }

                    $helpDate->addYearNoOverflow();
                }
            }

            Salary::where('user_id', $this->id)
                ->whereNotIn('buy_at', $ages)
                ->where('buy_at', '>=', $startDate)
                ->chunkById(1000, function ($items) use (&$deletedData) {
                    foreach ($items as $item) {
                        $deletedData[] = $item->buy_at->subYear()->format('Y');

                        $item->delete();
                    }
                });

            $data['user_id'] = $this->id;
            $updatePlan['user'] = [
                'percent_positive' => $this->percent_positive,
                'percent_neutral' => $this->percent_neutral,
                'percent_negative' => $this->percent_negative,
                'index_positive_income' => $this->index_positive_income,
                'index_neutral_income' => $this->index_neutral_income,
                'index_negative_income' => $this->index_negative_income,
                'index_outcome' => $this->index_outcome,
                'start_enter_at' => $this->start_enter_at,
                'retired_age' => $this->retired_age,
                'dead_age' => $this->dead_age,
                'career_start_month' => $this->career_start_month,
            ];
            if ($data) {
                $updatePlan['plan'] = $data;
            }
            if ($deletedData) {
                $updatePlan['deleted_plan'] = $deletedData;
            }

            DB::table('user_update_plans')
                ->insert([
                    'created_at' => Carbon::now(),
                    'user_id' => $this->id,
                    'data' => json_encode($updatePlan),
                    'type_id' => ActiveGoal::CRT,
                ]);
        }
    }
}