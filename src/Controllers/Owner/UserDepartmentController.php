<?php

namespace Common\Controllers\Owner;

use App\Api\V1\Controllers\Common\BaseController;
use Common\Models\Users\Departments\Department;
use Auth;
use Common\Models\Users\Departments\UserDepartment;
use DB;

class UserDepartmentController extends BaseController
{
    /**
     * @var string
     */
    public $modelName = UserDepartment::class;

    /**
     * @var string[]
     */
    public $onlyFieldsCreate = [
        'department_id',
        'user_id',
    ];

    /**
     * @var string[]
     */
    public $onlyFieldsUpdate = [
        'department_id',
        'user_id',
    ];

    /**
     * @var array
     */
    public $validators = [
//        'postStore' => CrmApplicationStoreRequest::class,
//        'putUpdate' => CrmApplicationUpdateRequest::class,
//        'deleteDestroy' => CrmStatusContactDeleteRequest::class,
    ];

    /**
     * @param $request
     */
    public function queryCondition($request)
    {
        $this->modelQuery->where('user_id', Auth::id());;
    }

    /**
     * @param $request
     *
     * @return mixed
     */
    public function postStore($request)
    {
        return DB::transaction(function () use ($request) {
            return $this->response()->error('Ошибка в получении данных');
        }, config('app.transaction_tries'));
    }

    /**
     * @param $request
     *
     * @return mixed
     */
    public function putUpdate($request)
    {
        return DB::transaction(function () use ($request) {
            return $this->response()->error('Ошибка в получении данных');
        }, config('app.transaction_tries'));
    }

    /**
     * @param $request
     *
     * @return mixed
     */
    public function deleteRemove($request)
    {
        return DB::transaction(function () use ($request) {
            return $this->response()->error('Ошибка в получении данных');
        }, config('app.transaction_tries'));
    }
}