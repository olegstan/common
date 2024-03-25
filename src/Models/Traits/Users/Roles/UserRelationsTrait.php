<?php
namespace Common\Models\Traits\Users\Roles;

use App\Models\Accounts\UserAccount;
use App\Models\Actives\Active;
use App\Models\Actives\ActiveGoal;
use App\Models\Actives\ActiveGroup;
use App\Models\Aton\AtonCommission;
use App\Models\Aton\AtonOperation;
use App\Models\Bcs\BcsCommission;
use App\Models\Bcs\BcsOperation;
use App\Models\Collective\CollectiveGroup;
use App\Models\CreditLog;
use App\Models\Tinkoff\TinkoffOperation;
use App\Models\Tinkoff\TinkoffOrder;
use App\Models\Transfers\Transfer;
use Common\Models\Users\Crm\UserDevice;
use Common\Models\Users\Crm\UserFinanceGroup;
use Common\Models\Users\Notification\UserNotification;
use Common\Models\Users\UserCreditLog;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait UserRelationsTrait
{
    /**
     * @return HasMany
     */
    public function actives(): HasMany
    {
        return $this->hasMany(Active::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function goal_groups(): HasMany
    {
        return $this->hasMany(ActiveGroup::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function goals(): HasMany
    {
        return $this->hasMany(ActiveGoal::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function user_accounts(): HasMany
    {
        return $this->hasMany(UserAccount::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(CreditLog::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function userLogs(): HasMany
    {
        return $this->hasMany(UserCreditLog::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function tinkoff_orders(): HasMany
    {
        return $this->hasMany(TinkoffOrder::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class, 'user_id');
    }


    /**
     * @return HasMany
     */
    public function atonOperations(): HasMany
    {
        return $this->hasMany(AtonOperation::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function atonCommissions(): HasMany
    {
        return $this->hasMany(AtonCommission::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function tinkoffOperations(): HasMany
    {
        return $this->hasMany(TinkoffOperation::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function bcsOperations(): HasMany
    {
        return $this->hasMany(BcsOperation::class, 'user_id');
    }
    /**
     * @return HasMany
     */
    public function bcsCommissions(): HasMany
    {
        return $this->hasMany(BcsCommission::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function collectiveUser(): HasMany
    {
        return $this->hasMany(CollectiveGroup::class, 'user_id');
    }

    /**
     * @return HasOne
     */
    public function collectiveUnionUser(): HasOne
    {
        return $this->hasOne(CollectiveGroup::class, 'union_user_id');
    }

    /**
     * @return HasMany
     */
    public function financeGroups(): HasMany
    {
        return $this->hasMany(UserFinanceGroup::class, 'user_id');
    }

    /**
     * @return HasOne
     */
    public function oppositeFinanceGroup(): HasOne
    {
        return $this->hasOne(UserFinanceGroup::class, 'union_user_id');
    }
    /**
     * @return HasMany
     */
    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class, 'user_id');
    }
}