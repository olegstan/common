<?php

namespace Common\Models\Users\Roles;

use Common\Models\Traits\Users\Roles\PermissionHasRelations;
use Common\Models\Traits\Users\Roles\Slugable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property $name
 * @property $slug
 * @property $description
 * @property $model
 */
class Permission extends Model
{
    use Slugable, PermissionHasRelations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'model'
    ];

    /**
     * Create a new model instance.
     *
     * @param array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if ($connection = config('roles.connection')) {
            $this->connection = $connection;
        }
    }
}
