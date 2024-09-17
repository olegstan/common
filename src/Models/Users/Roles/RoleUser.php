<?php

namespace Common\Models\Users\Roles;

use Common\Models\BaseModel;
use Common\Models\Traits\Users\Roles\RoleHasRelations;
use Common\Models\Traits\Users\Roles\Slugable;
use Illuminate\Database\Eloquent\Model;

class RoleUser extends BaseModel
{
    use Slugable, RoleHasRelations;

    /**
     * @var string
     */
    public $table = 'role_users';

    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
        'role_id',
        'user_id'
    ];
}
