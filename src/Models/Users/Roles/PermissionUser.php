<?php

namespace Common\Models\Users\Roles;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PermissionUser
 * @package Common\Models\Users\Roles
 */
class PermissionUser extends Model
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
