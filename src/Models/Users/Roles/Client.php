<?php

namespace Common\Models\Users\Roles;

use App\Models\Accounts\UserAccount;
use App\Models\Actives\Active;
use App\Models\Actives\ActiveGroup;
use Common\Models\Traits\Users\Roles\Client\DividendTrait;
use Common\Models\Traits\Users\Roles\Client\TacticsTrait;
use Common\Models\Traits\Users\Roles\Client\TransactionTrait;
use Common\Models\Traits\Users\Roles\Client\ValueTrait;
use Common\Models\Traits\Users\StrategyTrait;
use Common\Models\Users\User;
use App\Traits\Models\User\UserRelationsTrait;
use Carbon\Carbon;
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
 * @package App\Models\Users
 */
class Client extends User
{
    use StrategyTrait, TacticsTrait, TransactionTrait, ValueTrait, DividendTrait, UserRelationsTrait;

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
     * @return HasMany
     */
    public function groups()
    {
        return $this->hasMany(ActiveGroup::class, 'user_id');
    }
}
