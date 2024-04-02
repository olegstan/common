<?php

namespace Common\Observers\Users\Departments;


use Common\Models\Users\Collective\UserCollectiveGroup;
use Common\Models\Users\Departments\UserDepartment;

class UserDepartmentObserver
{
    /**
     * @param UserDepartment $data
     *
     * @return void
     */
    public function creating(UserDepartment $data)
    {
        UserCollectiveGroup::create([
            'user_id' => $data->department->user->id,
            'union_user_id' => $data->user_id,
            'type_id' => UserCollectiveGroup::EMPLOYEE
        ]);
    }

    /**
     * @param UserDepartment $data
     *
     * @return void
     */
    public function created(UserDepartment $data)
    {

    }

    /**
     * @param UserDepartment $data
     *
     * @return void
     */
    public function updated(UserDepartment $data)
    {
    }

    /**
     * @param UserDepartment $data
     *
     * @return void
     */
    public function deleted(UserDepartment $data)
    {

    }

    /**
     * @param UserDepartment $data
     *
     * @return void
     */
    public function deleting(UserDepartment $data)
    {
    }
}