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
use Common\Models\Traits\Users\Roles\HasRoleAndPermission;
use Common\Models\Traits\Users\Roles\UserAttributeTrait;
use Common\Models\Traits\Users\Roles\UserPathTrait;
use Common\Models\Traits\Users\Roles\UserPlanTrait;
use Common\Models\Traits\Users\Roles\UserRelationsTrait;
use Common\Models\Traits\Users\StrategyTrait;
use Common\Models\Traits\Users\UserConfigTrait;
use Common\Models\Traits\Users\UserTrait;
use Common\Models\Users\Crm\UserConfig;
use Common\Models\Users\Roles\Role;
use DB;
use Exception;
use File;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;

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
    use UserTrait;
    use UserConfigTrait;

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

    public const DEFAULT_RETIRE_AGE = 60;
    public const DEFAULT_DEAD_AGE = 25;

    public const DEFAULT_INDEX = 0;
    public const DEFAULT_PERCENT_POSITIVE = 30;
    public const DEFAULT_PERCENT_NEUTRAL = 50;
    public const DEFAULT_PERCENT_NEGATIVE = 20;

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
     * @return HasMany
     */
    public function configs(): HasMany
    {
        return $this->hasMany(UserConfig::class, 'user_id');
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

    /**
     * Возвращает идентификатор пользователя с префиксом приложение (production-322)
     *
     * @param $userId
     *
     * @return string
     */
    public static function getAppUser($userId): string
    {
        return config('app.env') . '-' . $userId;
    }
}