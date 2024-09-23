<?php

namespace Common\Models\Users;


use Common\Models\Users\Collective\UserCollectiveGroup;
use Common\Models\Users\Roles\Types\Client;

class Employee extends User
{
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
}