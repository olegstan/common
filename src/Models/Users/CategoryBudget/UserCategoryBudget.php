<?php

namespace Common\Models\Users\CategoryBudget;

use App\Traits\Models\RemoveActives\RemoveActiveByUserId;
use Common\Models\BaseModel;
use Common\Models\Interfaces\CommonRemoveActiveInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class UserCategoryBudget
 */
class UserCategoryBudget extends BaseModel implements CommonRemoveActiveInterface
{
    use RemoveActiveByUserId;

    /**
     * @var string
     */
    public $table = 'user_category_budgets';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'is_active',
        'name',
    ];

    /**
     * @var array
     */
    protected $casts = [

    ];

    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(UserCategoryBudgetItem::class, 'parent_id');
    }
}
