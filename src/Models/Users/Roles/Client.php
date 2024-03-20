<?php

namespace Common\Models\Users\Roles;

use App\Models\Accounts\UserAccount;
use App\Models\Actives\Active;
use App\Models\Actives\ActiveGroup;
use App\Models\Crm\Contact\CrmContact;
use App\Models\Crm\CrmApplication;
use Common\Models\Traits\Users\Roles\Client\DividendTrait;
use Common\Models\Traits\Users\Roles\Client\TacticsTrait;
use Common\Models\Traits\Users\Roles\Client\TransactionTrait;
use Common\Models\Traits\Users\Roles\Client\ValueTrait;
use Common\Models\Traits\Users\Roles\UserRelationsTrait;
use Common\Models\Traits\Users\StrategyTrait;
use Common\Models\Users\User;
use Carbon\Carbon;
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
 * @package App\Models\Users
 */
class Client extends User
{
    use StrategyTrait;
    use TacticsTrait;
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
     * @param array $data
     * @return false|void
     */
    public function createApplicationAndContactFromClient(array $data = [])
    {
        if (!$this->email || trim($this->email) === '') {
            return false;
        }

        $contact = CrmContact::create(array_merge(
            $this->attributes, [
            'user_id' => $this->manager_id,//должен быть id менеджера
            'attitude' => ($data['attitude'] ?? ''),
        ]));

        if ($contact) {
            $this->contact_id = $contact->id;
            $this->save();
            CrmApplication::add([
                'responsible_user_id' => $this->manager_id,
                'status_id' => CrmApplication::CONTRACT_CONFIRMED,
                'source_id' => CrmApplication::SOURCE_CHANNEL,
                'duration' => Carbon::now(),
                'contacts' => [
                    [
                        'is_beneficiary' => 1,
                        'contact_id' => $contact->id,
                    ]
                ]
            ]);
        }
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
}
