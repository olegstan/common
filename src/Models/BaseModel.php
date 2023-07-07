<?php

namespace Common\Models;

use Carbon\Carbon;
use Common\Models\Traits\BaseTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Sofa\Eloquence\Subquery;

/**
 * Class BaseModel
 */
class BaseModel extends Model
{
    use BaseTrait;

    protected $connection = 'mysql';

    public function __construct(array $attributes = [])
    {
        $this->table = config('database.connections.mysql.database') . '.' . $this->table;
        parent::__construct($attributes);
    }
}