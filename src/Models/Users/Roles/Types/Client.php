<?php

namespace Common\Models\Users\Roles\Types;

use App\Models\Accounts\Types\Cash;
use App\Models\Accounts\UserAccount;
use App\Models\Accounts\UserSubaccount;
use App\Models\Actives\Active;
use App\Models\Actives\ActiveGoal;
use App\Models\Actives\ActiveGoalPayment;
use App\Models\Actives\ActiveGroup;
use App\Models\Crm\Contact\CrmContact;
use App\Models\Crm\CrmApplication;
use Carbon\Carbon;
use Common\Models\Currency;
use Common\Models\Traits\Users\Roles\Client\DividendTrait;
use Common\Models\Traits\Users\Roles\Client\TransactionTrait;
use Common\Models\Traits\Users\Roles\Client\ValueTrait;
use Common\Models\Traits\Users\Roles\UserRelationsTrait;
use Common\Models\Traits\Users\StrategyTrait;
use Common\Models\Users\User;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Manager
 *
 * @property Collection|Active[] $actives
 * @property Collection|ActiveGroup[] $groups
 * @property Collection|UserAccount[] $user_accounts
 * @property integer $id
 *
 * @package Common\Models\Users
 */
class Client extends User
{
    use StrategyTrait;
    use TransactionTrait;
    use ValueTrait;
    use DividendTrait;
    use UserRelationsTrait;

    /**
     * @var array
     */
    public $points = [];

    /**
     * @var string
     */
    public $role = User::CLIENT;

    /**
     * @var bool
     */
    public $needCallCreatePlan = false;

    public function addPoint(Carbon $date, $color, $text)
    {
        $this->points[] = [
            'year' => $date->year,
            'month' => $date->month,
            'day' => $date->day,
            'color' => $color,
            'text' => $text,
        ];
    }

    /**
     * @param $query
     */
    public function scopeTasksOrder($query)
    {
        //группировкао по пользователю + в группировку берем последнее значение из end_at по данному контакту, чтобы сортировать
        $query->leftJoin('crm_contacts', 'crm_contacts.id', '=', 'users.contact_id')
            ->leftJoin('crm_contact_tasks', function ($join) {
                $join->on('crm_contact_tasks.contact_id', '=', 'users.contact_id')
                    ->on('crm_contact_tasks.end_at', '=', DB::raw('(SELECT MAX(end_at) FROM crm_contact_tasks WHERE contact_id = users.contact_id)'));
            })
            ->groupBy('users.id');
    }

    /**
     * @return HasMany
     */
    public function groups()
    {
        return $this->hasMany(ActiveGroup::class, 'user_id');
    }

    /**
     * @return void
     */
    public function createAccounts()
    {
        $currencyRub = Currency::getByCode(Currency::RUB);
        $currencyUsd = Currency::getByCode(Currency::USD);
        $currencyEur = Currency::getByCode(Currency::EUR);

        $cash = Cash::create([
            'user_id' => $this->id,
            'name' => 'Кошелек',
        ]);

        UserSubaccount::create([
            'account_id' => $cash->id,
            'currency_id' => $currencyRub->id,
            'sum' => 0,
            'name' => 'Наличные RUB',
        ]);

        UserSubaccount::create([
            'account_id' => $cash->id,
            'currency_id' => $currencyUsd->id,
            'sum' => 0,
            'name' => 'Наличные USD',
        ]);

        UserSubaccount::create([
            'account_id' => $cash->id,
            'currency_id' => $currencyEur->id,
            'sum' => 0,
            'name' => 'Наличные EUR',
        ]);
    }

    /**
     * @param $currency
     *
     * @return void
     * @throws Throwable
     */
    public function recalculateGoals($currency)
    {
        $dispatcher = ActiveGoal::getEventDispatcher();

        ActiveGoal::unsetEventDispatcher();

        /**
         * @var ActiveGoal $goals []
         */
        $goals = ActiveGoal::where('user_id', $this->id)
            ->orderBy('order')
            ->with('goal_payments', 'items')
            ->get();

        $index = [];
        $payments = [];
        $paymentsIndex = [];
        $paymentsDays = [];

        $salaries = ActiveGoal::prepareSalaries($this, $index);

        foreach ($goals as $goal) {
            DB::transaction(function () use ($goal, $currency, $salaries, $paymentsDays, $payments, $paymentsIndex, $index) {
                    /**
                     * @var ActiveGoal $goal
                     */
                    $data = object_to_array(json_decode($goal->data));

                    if (is_array($data)) {
                        foreach ($data as $k => $item) {
                            //должны быть указаны оба параметра чтобы не было пустых массивов
                            if (isset($item['sum'])) {
                                $data[$k]['sum'] = $currency->convert(
                                    $data[$k]['sum'],
                                    $goal->currency_id,
                                    Carbon::now(),
                                );
                            }
                            if (isset($item['payment_per_period'])) {
                                $data[$k]['payment_per_period'] = $currency->convert(
                                    $data[$k]['payment_per_period'],
                                    $goal->currency_id,
                                    Carbon::now(),
                                );
                            }
                            if (isset($item['payment_per_period_first'])) {
                                $data[$k]['payment_per_period_first'] = $currency->convert(
                                    $data[$k]['payment_per_period_first'],
                                    $goal->currency_id,
                                    Carbon::now(),
                                );
                            }
                            if (isset($item['payment_per_period_last'])) {
                                $data[$k]['payment_per_period_last'] = $currency->convert(
                                    $data[$k]['payment_per_period_last'],
                                    $goal->currency_id,
                                    Carbon::now(),
                                );
                            }
                            if (isset($item['future_sum'])) {
                                $data[$k]['future_sum'] = $currency->convert(
                                    $data[$k]['future_sum'],
                                    $goal->currency_id,
                                    Carbon::now(),
                                );
                            }
                        }
                    }

                    //считаем сколько денег уже привязано к цели
                    $alreadyPaid = 0;

                    foreach ($goal->goal_payments as $payment) {
                        /**
                         * @var ActiveGoalPayment $payment
                         */
                        if ($payment->paid_sum > 0) {
                            $alreadyPaid += $payment->paid_sum;
                        }
                    }

                    foreach ($goal->items as $item) {
                        $item->updateQuietly([
                            'sum' => round($currency->convert($item->sum, $goal->currency_id, Carbon::now()), 2),
                        ]);
                    }

                    //удаляем
                    ActiveGoalPayment::where('goal_id', $goal->id)
                        ->delete();

                    $goal->updateQuietly([
                        'spending_per_period' => round(
                            $currency->convert($goal->spending_per_period, $goal->currency_id, Carbon::now()),
                            2,
                        ),
                        'spending_per_period_future' => round(
                            $currency->convert($goal->spending_per_period_future, $goal->currency_id, Carbon::now()),
                            2,
                        ),
                        'future_sum' => round(
                            $currency->convert($goal->future_sum, $goal->currency_id, Carbon::now()),
                            2,
                        ),
                        'start_sum' => round($currency->convert($alreadyPaid, $goal->currency_id, Carbon::now()), 2),
                        'data' => json_encode($data),
                        'currency_id' => $currency->id,
                    ]);


                    if ($goal->type_id === ActiveGoal::WITHOUT_PLAN) {
                        switch ($goal->type_id) {
                            case ActiveGoal::SHORT:
                                $goal->calcShortGoal();
                                break;
                            case ActiveGoal::MIDDLE:
                            case ActiveGoal::LONG:
                                $goal->calcLongGoal();
                                break;
                            case ActiveGoal::RETIRE:
                                $goal->calcRetiredGoal();
                                break;
                        }
                    } else {
                        $startDate = $goal->start_at;

                        $retiredYear = $this->birth_at->addYears($this->retired_age)->format('Y');

                        $firstYear = null;

                        ActiveGoal::preparePaymentDays(
                            $paymentsDays,
                            $payments,
                            $paymentsIndex,
                            $salaries,
                            $firstYear,
                            $retiredYear,
                        );

                        $data = $goal->prepareCalcData($this, $firstYear, $retiredYear, $paymentsDays, $startDate);

                        [$result, $resultFile] = ActiveGoal::calcGoal($data, $goal->id);

                        if ($result) {
                            $resultData = object_to_array(json_decode(File::get($resultFile)));

                            $payments = object_to_array(json_decode($goal->payments));
                            $payments['aim_payments_index'] = $resultData['aim_payments_index'];
                            $payments['aim_payments_values'] = $resultData['aim_payments_values'];

                            $goal->updateQuietly([
                                'payments' => json_encode($payments),
                            ]);
                        }
                    }
                },
            );
        }

        ActiveGoal::setEventDispatcher($dispatcher);
    }
}
