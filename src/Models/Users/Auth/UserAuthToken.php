<?php

namespace Common\Models\Users\Auth;

use Common\Models\BaseModel;
use Common\Models\Users\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Class UserAuthToken
 *
 * @property $user_id
 * @property $token
 *
 * @package Common\Models\Users
 */
class UserAuthToken extends BaseModel
{
    /**
     * @var string
     */
    public $table = 'user_auth_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
		'user_id',
		'token',
	];

    /**
     * @return BelongsTo
     */
	public function user(): BelongsTo
    {
		return $this->belongsTo(User::class, 'user_id');
	}

    /**
     * @param $field
     * @param $count
     * @return string
     */
    public static function getUniqueHash($field, $count = 100): string
    {
        $hash = Str::random($count);
        $item = static::where($field, $hash)
            ->first();
        if ($item) {
            return self::getUniqueHash($field, $count);
        }

        return $hash;
    }
}