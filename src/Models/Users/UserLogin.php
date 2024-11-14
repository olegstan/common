<?php

namespace Common\Models\Users;

use App\Helpers\Email;
use Common\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserLogin
 *
 * @property $user_id
 * @property $ip
 * @property $country
 *
 */
class UserLogin extends BaseModel
{
    /**
     * @var string
     */
    public $table = 'user_logins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
		'user_id',
		'ip',
		'country',
	];
	
    /**
     * @var bool
     */
    protected $timestamp = true;

    /**
     * @return \Common\Models\Traits\BelongsTo
     */
	public function user()
    {
		return $this->belongsTo(User::class, 'user_id');
	}

    /**
     * @param $user
     * @param bool $sendMail
     *
     * @return \Common\Models\Users\UserLogin|BaseModel|Model
     */
    public static function logLogin($user, bool $sendMail = false)
    {
        $user_ip = ($_SERVER["HTTP_CF_CONNECTING_IP"] ?? ($_SERVER['REMOTE_ADDR'] ?? null));
        $user_country = ($_SERVER["HTTP_CF_IPCOUNTRY"] ?? null);

        $logins = UserLogin::where('user_id', $user->id)
            ->groupBy('ip')
            ->pluck('ip')
            ->toArray();

        if (!in_array($user_ip, $logins) && $sendMail) {
            Email::sendOne('loginnewip', [
                'to' => $user->email,
                'title' => 'Выполнен вход с нового ip-адреса',
                'ip' => $user_ip,
            ]);
        }

        return UserLogin::create([
            'user_id' => $user->id,
            'ip' => $user_ip,
            'country' => $user_country,
        ]);
    }
}