<?php

namespace Common\Models\Users\Roles;

use Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PermissionUser
 * @package Common\Models\Users\Roles
 */
class PermissionUser extends BaseModel
{
    /**
     * @var string 
     */
    public $table = 'permission_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'permission_id',
        'user_id',
    ];
}
