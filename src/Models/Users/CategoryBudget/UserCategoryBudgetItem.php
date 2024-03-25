<?php

namespace Common\Models\Users\CategoryBudget;

use Carbon\Carbon;
use Common\Models\BaseModel;
use Common\Models\Interfaces\CommonRemoveActiveInterface;
use DB;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class UserCategoryBudgetItem
 */
class UserCategoryBudgetItem extends BaseModel implements CommonRemoveActiveInterface
{
    /**
     * @var string
     */
    public $table = 'user_category_budget_items';

    /**
     * @var array
     */
    protected $fillable = [
        'type_id',
        'custom_type_id',
        'sum',
        'parent_id',
    ];

    /**
     * @var array
     */
    protected $casts = [

    ];

    /**
     * @return HasOne
     */
    public function parent(): HasOne
    {
        return $this->hasOne(UserCategoryBudget::class, 'id', 'parent_id');
    }

    /**
     * @param $user
     * @param $collections
     * @return void
     */
    public function selfRemoveData($user, $collections): void
    {
        $categoryBudgets = UserCategoryBudget::whereUserId($user->id)->pluck('id');
        $selfData = $this->whereIn('parent_id', $categoryBudgets)->get();

        foreach ($selfData as $data) {
            $collections->put($this->getTableWithoutPrefix() . '.' . $data->id, json_encode($data));
        }
    }

    /**
     * @param $userId
     * @return void
     */
    public static function jsonBudget($userId)
    {
        $actBudget = UserCategoryBudget::where('user_id', $userId)
            ->where('is_active', 1)
            ->with('items')
            ->first();

        if ($actBudget) {

            $jsonData = json_encode($actBudget);

            DB::table('user_update_budgets')
                ->insert([
                    'user_id' => $userId,
                    'data' => $jsonData,
                    'created_at' => Carbon::now(),
                ]);

        }
    }
}
