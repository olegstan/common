<?php
namespace Common\Models\Traits\Users\Roles;

use App\Models\ZenMoney\ZenMoneyUser;
use Cache;

trait UserAttributeTrait
{
    /**
     * @param $date
     * @return void
     */
    public function setBirthAtAttribute($date)
    {
        $this->setDate('birth_at', $date);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getZenmoneyDataAttribute($value)
    {
        if($value)
        {
            return object_to_array(json_decode($value));
        }

        return [];
    }

    /**
     * @param $data
     * @return void
     */
    public function setZenmoneyDataAttribute($data)
    {
        //если там и так строка, то не конвертим второй раз, чтобы не конвертнуть json в json
        if (isset($data) && !is_string($data)) {
            $this->attributes['zenmoney_data'] = json_encode($data);
        }else{
            $this->attributes['zenmoney_data'] = $data;
        }
    }

    /**
     * @return array|mixed|void
     */
    public function getCountZenmoneyDataAttribute()
    {
        if($this->zenmoney_data)
        {
            $array = $this->zenmoney_data;

            if(is_countable($array))
            {
                return count($array);
            }
        }

        return 0;
    }

    /**
     * @return array
     */
    public function getZenmoneyDataWithLoginsAttribute()
    {
        $zenmoneyData = [];

        if($this->zenmoney_data)
        {
            $array = $this->zenmoney_data;

            if(is_array($array))
            {
                foreach ($array as $token)
                {
                    if(isset($token['user_id']))
                    {
                        $cacheKey = 'zenmoeney_user.' . $token['user_id'];
                        $zenUserLogin = Cache::tags(['back'])->rememberForever($cacheKey, function () use ($token)
                        {
                            $zenUser = ZenMoneyUser::where('zenmoney_id', $token['user_id'])
                                ->first();

                            if($zenUser)
                            {
                                return $zenUser->login ?? $zenUser->zenmoney_id;
                            }
                        });

                        if($zenUserLogin)
                        {
                            $zenmoneyData[$token['user_id']] = $zenUserLogin;
                        }else{
                            Cache::tags(['back'])->forget($cacheKey);//не нашло логин, значит чистим кэш
                        }
                    }
                }
            }
        }

        return $zenmoneyData;
    }
}