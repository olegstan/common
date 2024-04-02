<?php

namespace Common\Controllers\Owner;

use App\Api\V1\Controllers\Common\BaseController;
use Common\Models\Users\Departments\Department;
use Auth;
use DB;

class DepartmentController extends BaseController
{
    /**
     * @var string
     */
    public $modelName = Department::class;

    /**
     * @var string[]
     */
    public $onlyFieldsCreate = [
        'user_id',
        'name',
        'description',
    ];

    /**
     * @var string[]
     */
    public $onlyFieldsUpdate = [
        'user_id',
        'name',
        'description',
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
            $department = Department::create([
                'user_id' => Auth::id(),
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);

            if ($department) {
                return $this->response()->sucess('Отдел успешно создан');
            }

            return $this->response()->error('Ошибка создания отдела');
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
            if (!$department = Department::find($request->input('department_id'))) {
                return $this->response()->error('Такого отдела не существует');
            }

            $upd = $department->update([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);

            if ($upd) {
                return $this->response()->sucess('Данные успешно обновлены');
            }

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
            if (!$department = Department::find($request->input('department_id'))) {
                return $this->response()->error('Такого отдела не существует');
            }

            if ($department->delete()) {
                return $this->response()->sucess('Отдел успешно удален');
            }

            return $this->response()->error('Ошибка в получении данных');
        }, config('app.transaction_tries'));
    }

    /**
     * @return void
     */
    public function getAllDepartment($request)
    {
        return DB::transaction(function () use ($request) {

        }, config('app.transaction_tries'));
    }
}