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
class Base extends Model
{
   use BaseTrait;
}