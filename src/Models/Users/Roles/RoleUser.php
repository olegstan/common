<?php

namespace Common\Models\Users\Roles;

use Common\Models\BaseModel;
use Common\Models\Traits\Users\Roles\RoleHasRelations;
use Common\Models\Traits\Users\Roles\Slugable;
use Common\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RoleUser extends BaseModel
{
    use Slugable, RoleHasRelations;

    /**
     * @var string
     */
    public $table = 'role_user';

    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
        'role_id',
        'user_id'
    ];

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return HasMany
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'id', 'role_id');
    }
}
