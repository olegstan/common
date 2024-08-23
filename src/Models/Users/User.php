<?php

namespace Common\Models\Users;

use App\Models\Accounts\Types\Cash;
use App\Models\Accounts\UserSubaccount;
use App\Models\Actives\ActiveGoal;
use App\Models\Actives\ActiveGoalPayment;
use App\Models\Aton\AtonUser;
use App\Models\Crm\Contact\CrmContact;
use App\Models\Crm\CrmApplication;
use Carbon\Carbon;
use Common\Casts\BoolCast;
use Common\Casts\IntegerCast;
use Common\Casts\StringCast;
use Common\Models\BaseModel;
use Common\Models\Currency;
use Common\Models\Traits\Users\Roles\HasRoleAndPermission;
use Common\Models\Traits\Users\Roles\UserAttributeTrait;
use Common\Models\Traits\Users\Roles\UserPathTrait;
use Common\Models\Traits\Users\Roles\UserPlanTrait;
use Common\Models\Traits\Users\Roles\UserRelationsTrait;
use Common\Models\Traits\Users\StrategyTrait;
use Common\Models\Users\Collective\UserCollectiveGroup;
use Common\Models\Users\Crm\UserConfig;
use Common\Models\Users\Roles\Role;
use Common\Models\Users\Roles\Types\Client;
use DB;
use Exception;
use File;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HigherOrderBuilderProxy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

/**
 * Class User
 *
 * @property $data
 * @property $avatar
 * @property $is_new
 * @property double $balance
 * @property double $points
 * @property Carbon $birth_at
 * @property integer $retired_age
 * @property integer $dead_age
 * @property Carbon $start_enter_at
 * @property float $percent_positive
 * @property float $percent_neutral
 * @property float $percent_negative
 * @property float $index_positive_income
 * @property float $index_neutral_income
 * @property float $index_negative_income
 * @property float $index_outcome
 * @property float $career_start_month
 * @property string $session
 * @property string $is_imported
 * @property string $promo_code
 * @property string $tinkoff_token
 * @property string $gauth_trigger
 * @property string $tinkoff_mode
 * @property string $is_visible_spend
 * @property string $is_allow_api_operation
 * @property integer $manager_id
 * @property integer $currency_id
 * @property integer $language_id
 * @property string $email
 * @property string $phone
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string $google_data
 * @property int $count_zenmoney_data
 * @property array $zenmoney_data_with_logins
 *
 * @mixin StrategyTrait
 *
 */
class User extends BaseModel implements AuthenticatableContract, CanResetPasswordContract
{
    use HasFactory;
    use Authenticatable;
    use CanResetPassword;
    use HasRoleAndPermission;
    use UserPlanTrait;
    use UserRelationsTrait;
    use UserAttributeTrait;
    use UserPathTrait;

    public const CONF_NOTIFICATION_DELETE_AFTER_NEVER = 1001;
    public const CONF_NOTIFICATION_DELETE_AFTER_DAY_END = 1002;
    public const CONF_NOTIFICATION_DELETE_AFTER_WEEK = 1003;
    public const CONF_NOTIFICATION_DELETE_AFTER_TWO_WEEKS = 1004;
    public const CONF_NOTIFICATION_DELETE_AFTER_MONTH = 1005;

    public const MANAGER = 'manager';
    public const OWNER = 'owner';
    public const DIRECTOR = 'director';
    public const ASSISTANT = 'assistant';
    public const ACCOUNTANT = 'accountant';
    public const PARTNER = 'partner';
    public const DRIVER = 'driver';
    public const CLIENT = 'client';

    public const MANAGER_GROUP = [
        self::MANAGER,
        self::OWNER,
        self::DIRECTOR,
        self::ASSISTANT,
        self::ACCOUNTANT,
        self::PARTNER,
        self::DRIVER,
    ];

    public static $avatarPath = '/images/avatar/';
    public static $documentPath = '/storage/document/';

    private array $aton_configs = [];
    private array $client_ids = [];

    public const DEFAULT_RETIRE_AGE = 60;
    public const DEFAULT_DEAD_AGE = 25;

    public const DEFAULT_INDEX = 0;
    public const DEFAULT_PERCENT_POSITIVE = 30;
    public const DEFAULT_PERCENT_NEUTRAL = 50;
    public const DEFAULT_PERCENT_NEGATIVE = 20;


    //-----------------roles start-----------------------/

    /**
     * @param int|string $role
     *
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->getRoles()->contains(function ($value, $key) use ($role) {
            return $role == $value->id || Str::is($role, $value->slug);
        });
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        $roles = Role::all();
        foreach ($roles as $role) {
            if ($this->is($role->slug)) {
                return $role->slug;
            }
        }
    }

    /**
     * @return string
     */
    public function getFio()
    {
        return $this->last_name . ' ' . $this->first_name . ($this->middle_name ? ' ' . $this->middle_name : '');
    }

    //-----------------roles end-----------------------/

    /**
     * @var string
     */
    public $table = 'users';

    /**
     * @var string
     */
    public $role = false;

    protected $attributes = [
        'retired_age' => self::DEFAULT_RETIRE_AGE,
        'dead_age' => self::DEFAULT_DEAD_AGE,
        'percent_positive' => self::DEFAULT_PERCENT_POSITIVE,
        'percent_neutral' => self::DEFAULT_PERCENT_NEUTRAL,
        'percent_negative' => self::DEFAULT_PERCENT_NEGATIVE,
        'index_positive_income' => self::DEFAULT_INDEX,
        'index_neutral_income' => self::DEFAULT_INDEX,
        'index_negative_income' => self::DEFAULT_INDEX,
        'index_outcome' => self::DEFAULT_INDEX,
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'password',
        'email',
        'phone',
        'first_name',
        'last_name',
        'middle_name',
        'sex',
        'birth_at',
        'manager_id',
        'api_token',
        'data',
        'session',
        'avatar',
        'start_enter_at',
        'retired_age',
        'dead_age',

        'percent_positive',
        'percent_neutral',
        'percent_negative',
        'index_positive_income',
        'index_neutral_income',
        'index_negative_income',
        'index_outcome',
        'career_start_month',

        'balance',
        'points',

        'vk',
        'fb',
        'twit',

        'zenmoney_data',
        'google_data',

        'is_imported',
        'promo_code',

        'tinkoff_token',
        'tinkoff_mode',

        'gauth_secret',
        'gauth_trigger',
        'gauth_qr',

        'is_visible_spend',
        'is_allow_api_operation',
        'is_demo',
        'is_new',
        'currency_id',
        'contact_id',
        'language_id',
        'rating',
        'hidden_name',
        'phone_token',
        'operator_id',

        'salary',
        'bonus',
    ];


    /**
     * @var array
     */
    protected $casts = [
        'password' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'first_name' => 'string',
        'last_name' => 'string',
        'middle_name' => 'string',
        'comment' => 'string',
        'sex' => 'integer',
        'manager_id' => 'integer',
        'api_token' => 'string',
        'data' => 'string',
        'session' => 'string',
        'avatar' => 'string',
        'retired_age' => 'integer',
        'dead_age' => 'integer',

        'vk' => 'string',
        'fb' => 'string',
        'twit' => 'string',

        'google_data' => 'string',

        'percent_positive' => 'float',
        'percent_neutral' => 'float',
        'percent_negative' => 'float',
        'index_positive_income' => 'float',
        'index_neutral_income' => 'float',
        'index_negative_income' => 'float',
        'index_outcome' => 'float',
        'career_start_month' => 'integer',

        'is_imported' => 'boolean',
        'promo_code' => StringCast::class,
        'tinkoff_token' => StringCast::class,
        'tinkoff_mode' => 'integer',
        'is_visible_spend' => 'boolean',
        'is_allow_api_operation' => 'boolean',
        'currency_id' => IntegerCast::class,
        'contact_id' => IntegerCast::class,
        'language_id' => IntegerCast::class,
        'application_id' => IntegerCast::class,
        'application_max_delay_days' => IntegerCast::class,
        'notification_delete_after_value' => IntegerCast::class,
        'is_new' => 'boolean',
        'rating' => IntegerCast::class,
        'hidden_name' => BoolCast::class,
        'operator_id' => 'integer',
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'birth_at',
        'start_enter_at',
    ];

    /**
     * @return string
     */
    public static function avatarPath(): string
    {
        return public_path() . self::$avatarPath;
    }

    /**
     * @return string
     */
    public static function documentPath(): string
    {
        return public_path() . self::$documentPath;
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(config('roles.models.role'), 'role_user', 'user_id')->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function configs(): HasMany
    {
        return $this->hasMany(UserConfig::class, 'user_id');
    }

    /**
     * Получает значение конфигурации пользователя по ключу.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getConfigByKey(string $key = UserConfig::C_WEEK_HOLIDAYS)
    {
        $defaultConfig = UserConfig::UserDefaultConfigConstants[$key];
        $conf = $this->configs()->where('key', $key)->value('value');

        if (!$conf) {
            return $defaultConfig;
        }

        if (is_array($defaultConfig)) {
            // Добавил true, чтобы вернуть массив, а не объект
            return json_decode($conf, true);
        }

        if (is_numeric($defaultConfig)) {
            return (int)$conf;
        }

        return $conf;
    }

    /**
     * Возвращает все данные конфигурации Атон.
     *
     * @return array {
     *      aton_login: mixed,
     *      aton_pass: mixed,
     *      aton_group: mixed,
     *      aton_account: mixed,
     *      aton_path_to_2fa: mixed,
     * }
     */
    public function getAtonConfigs(): array
    {
        $atonConfigs = $this->aton_configs;

        if (empty($atonConfigs)) {
            $this->setAtonConfigs();
            return $this->getAtonConfigs();
        }

        return $atonConfigs;
    }

    /**
     * Записывает конфигурацию Атон
     *
     * @return $this
     */
    public function setAtonConfigs(): User
    {
        if (empty($this->aton_configs)) {
            $this->aton_configs = array_map([$this, 'getConfigByKey'], UserConfig::C_ATON_CONFIGS);
        }

        return $this;
    }

    /**
     * Возвращает все айдишники клиентов
     *
     * @return array
     */
    public function getClientIds(): array
    {
        $clientIds = $this->client_ids;

        if (empty($clientIds)) {
            $this->setClientIds();
            return $this->getClientIds();
        }

        return $clientIds;
    }

    /**
     * Записывает айдишники клиентов
     *
     * @return $this
     */
    public function setClientIds(): User
    {
        $managerIds = [$this->id];

        if ($this->getRole() === User::OWNER) {
            $managerIds = array_merge(
                $managerIds,
                UserCollectiveGroup::where('user_id', $this->id)
                    ->pluck('union_user_id')
                    ->toArray()
            );
        }

        if (in_array($this->getRole(), [User::MANAGER, User::OWNER])) {
            $this->client_ids = Client::whereIn('manager_id', $managerIds)
                ->pluck('id')
                ->toArray();
        }

        return $this;
    }


    /**
     * @return array
     */
    public function getContactIds()
    {
        switch ($this->getRole()) {
            case User::MANAGER:
                return CrmContact::where(function ($query) {
                    $query->where('user_id', $this->id);
                })
                    ->pluck('id')
                    ->toArray();
            case User::OWNER:
                $managerIds = UserCollectiveGroup::where('user_id', $this->id)
                    ->pluck('union_user_id')
                    ->toArray();

                return CrmContact::where(function ($query) use ($managerIds) {
                    $query->where('user_id', $this->id)
                        ->orWhereIn('user_id', $managerIds);
                })
                    ->pluck('id')
                    ->toArray();
                break;
            case User::DIRECTOR:

                break;
            case User::ASSISTANT:

                break;
            case User::ACCOUNTANT:

                break;
            case User::PARTNER:

                break;
            case User::DRIVER:

                break;
            case User::CLIENT:

                break;
        }

        return [];
    }

    /**
     * @param string $key
     * @param $value
     *
     * @return void
     */
    public function setConfigByKey(string $key = UserConfig::C_WEEK_HOLIDAYS, $value)
    {
        $conf = $this->configs()->where('key', '=', $key)->first();
        $newValue = $value;

        if (is_array($value)) {
            $newValue = json_decode($value);
        }
        if ($conf) {
            $conf->value = $newValue;
            $conf->save();
        } else {
            UserConfig::create(
                [
                    'user_id' => $this->id,
                    'key' => $key,
                    'value' => $newValue,
                ],
            );
        }
    }

    /**
     * @param array $attributes
     *
     * @return static
     * @throws Exception
     */
    public static function create(array $attributes = [])
    {
        $model = new static($attributes);
        $model->api_token = self::getUniqueHash('api_token');
        $model->save();

        $role = Role::where('slug', '=', $model->role)->get()->first();

        if ($role) {
            $model->attachRole($role);
        }

        return $model;
    }

    /**
     * @param $password
     *
     * @return bool
     */
    public function checkPassword($password)
    {
        if ($this->is_imported) {
            $salt = substr($this->password, 0, (strlen($this->password) - 32));
            $realPassword = substr($this->password, -32);

            $password = md5($salt . $password);

            return $password == $realPassword;
        }

        return Hash::check($password, $this->password);
    }

    /**
     * @return HasMany
     */
    public function atonUsers(): HasMany
    {
        return $this->hasMany(AtonUser::class, 'user_id');
    }

    /**
     * @return HasOne
     */
    public function contact(): HasOne
    {
        return $this->hasOne(CrmContact::class, 'id', 'contact_id');
    }

    /**
     * @return HasOne
     */
    public function mainContact(): HasOne
    {
        return $this->hasOne(CrmContact::class, 'id', 'contact_id');
    }

    /**
     * @return HasOne
     *
     * @deprecated в данный момент 14.09.2023 не проставляется в базе ни у одной записи
     */
    public function application(): HasOne
    {
        return $this->hasOne(CrmApplication::class, 'id', 'application_id');
    }

    /**
     * @param $query
     * @param $slug
     * @param bool $reverse
     *
     * @return mixed
     * @throws Exception
     */
    public function scopeRole($query, $slug, bool $reverse = false)
    {
        return $query->whereHas('roles', function ($j) use ($slug, $reverse) {
            switch (gettype($slug)) {
                case 'string':
                    $j->where('slug', $reverse ? '!=' : '=', $slug);
                    break;
                case 'array':
                    if ($reverse) {
                        $j->whereNotIn('slug', $slug);
                    } else {
                        $j->whereIn('slug', $slug);
                    }
                    break;
                default:
                    throw new Exception('Не верный тип данных');
            }
        });
    }

    /**
     * @return Builder
     */
    public function newQuery()
    {
        if ($this->role !== false) {
            return parent::newQuery()->role($this->role);
        }
        return parent::newQuery();
    }

    /**
     * @param $date
     */
    public function setBirthAtAttribute($date)
    {
        $this->setDate('birth_at', $date);
    }

    public function authData(): array
    {
        return [
            'configs' => $this->configs,
            'contacts' => CrmContact::where('user_id', '=', $this->id)
                ->with('requisite')
                ->with('requisite_bank')
                ->with('files')
                ->orderBy('id', 'DESC')
                ->get(),
        ];
    }

    /**
     * @return void
     */
    public function createWazzupUser(): void
    {
//        $url = 'https://api.wazzup24.com/v3/users';
//
//        $payload = [
//            [
//                'id' => (string)$this->id,
//                'name' => $this->last_name . ' ' . $this->first_name,
//                'phone' => str_replace(array('+', ' ', '(' , ')', '-'), '',  $this->phone)
//            ]
//        ];
//
//        WazzupControllerHelper::curlInit($url, 'post', $payload);
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
            DB::transaction(
                function () use ($goal, $currency, $salaries, $paymentsDays, $payments, $paymentsIndex, $index) {
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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return mixed|string|null
     */
    public function getTinkoffToken()
    {
        return $this->tinkoff_token;
    }

    /**
     * @return int|string
     */
    public function getTinkoffMode()
    {
        return $this->tinkoff_mode;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->last_name . ' ' . $this->first_name;
    }
}
