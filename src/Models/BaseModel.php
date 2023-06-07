<?php

namespace Common\Models;

use Carbon\Carbon;
use Exception;
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
    protected $connection = 'mysql';

    public function __construct(array $attributes = [])
    {
        $this->table = env('DB_DATABASE') . '.' . $this->table;
        parent::__construct($attributes);
    }
}