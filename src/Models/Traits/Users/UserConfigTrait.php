<?php
namespace Common\Models\Traits\Users;


use Common\Models\Users\Crm\UserConfig;
use Common\Models\Users\Roles\Types\Client;
use Common\Models\Users\User;

/**
 * Trait UserTrait
 *
 * @mixin Client
 *
 * @package Common\Models\Traits\Users
 */
trait UserConfigTrait
{
    /**
     * Получает значение конфигурации пользователя по ключу.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getConfigByKey(string $key = UserConfig::C_WEEK_HOLIDAYS)
    {
        $defaultConfig = UserConfig::UserDefaultConfigConstants[$key];
        $conf = $this->configs()->where('key', $key)->value('value');

        if (!$conf) {
            return $defaultConfig;
        }

        if (is_array($defaultConfig)) {
            // Добавил true, чтобы вернуть массив, а не объект
            return json_decode($conf, true);
        }

        if (is_numeric($defaultConfig)) {
            return (int)$conf;
        }

        return $conf;
    }

    /**
     * Возвращает все данные конфигурации Атон.
     *
     * @return array {
     *      aton_login: mixed,
     *      aton_pass: mixed,
     *      aton_group: mixed,
     *      aton_account: mixed,
     *      aton_path_to_2fa: mixed,
     * }
     */
    public function getAtonConfigs(): array
    {
        $atonConfigs = $this->aton_configs;

        if (empty($atonConfigs)) {
            $this->setAtonConfigs();
            return $this->getAtonConfigs();
        }

        return $atonConfigs;
    }

    /**
     * Записывает конфигурацию Атон
     *
     * @return $this
     */
    public function setAtonConfigs(): User
    {
        if (empty($this->aton_configs)) {
            $this->aton_configs = array_map([$this, 'getConfigByKey'], UserConfig::C_ATON_CONFIGS);
        }

        return $this;
    }

    /**
     * @param string $key
     * @param $value
     *
     * @return void
     */
    public function setConfigByKey(string $key = UserConfig::C_WEEK_HOLIDAYS, $value)
    {
        $conf = $this->configs()->where('key', '=', $key)->first();
        $newValue = $value;

        if (is_array($value)) {
            $newValue = json_decode($value);
        }
        if ($conf) {
            $conf->value = $newValue;
            $conf->save();
        } else {
            UserConfig::create(
                [
                    'user_id' => $this->id,
                    'key' => $key,
                    'value' => $newValue,
                ],
            );
        }
    }
}