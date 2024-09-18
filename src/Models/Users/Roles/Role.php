<?php

namespace Common\Models\Users\Roles;

use Common\Models\BaseModel;
use Common\Models\Traits\Users\Roles\RoleHasRelations;
use Common\Models\Traits\Users\Roles\Slugable;

class Role extends BaseModel
{
    use Slugable, RoleHasRelations;

    /**
     * @var string
     */
    public $table = 'roles';

    /**
     * В методе getRoles() как-то криво сделано, что в дальнейших методах ищется айдишник, а из-за fillable он не создается,
     * так что id должен присутствовать
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'slug',
        'description',
        'level'
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
