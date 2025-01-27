<?php

namespace Common\Models\Users\Crm;

use Common\Models\BaseModel;
use Common\Models\Interfaces\Users\Crm\UserConfigConstantsTrait;
use Common\Models\Traits\Users\UserConfigAttributeTrait;
use Common\Models\Users\User;
use InvalidArgumentException;

/**
 * Class UserConfig
 *
 * @property $user_id
 * @property $key
 * @property $value
 * @property $type
 *
 * @package Common\Models\Users\Crm
 */
class UserConfig extends BaseModel implements UserConfigConstantsTrait
{
    use UserConfigAttributeTrait;

    /**
     * @var string
     */
    public $table = 'user_configs';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'key',
        'value',
        'type',
    ];
    /**
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'key' => 'string',
        'value' => 'string',
        'type' => 'integer',
    ];

    /**
     * Создание записи в user_configs
     *
     * @param array $data [
     *     'user_id' => int,               // ID клиента (обязательное поле)
     *     'key' => string,               // Ключ (обязательное поле, например, 'aton')
     *     'login' => string|null,        // Логин (обязателен, если не переданы API ключи)
     *     'password' => string|null,     // Пароль (обязателен, если не переданы API ключи)
     *     'public_api_key' => string|null, // Публичный API ключ (обязателен, если нет логина и пароля)
     *     'private_api_key' => string|null // Приватный API ключ (необязательный)
     * ]
     *
     * @return static|null
     * @throws InvalidArgumentException
     */
    public static function createConfig(array $data): ?UserConfig
    {
        // Проверка обязательных параметров
        if (!array_key_exists('user_id', $data) || !array_key_exists('key', $data)) {
            throw new InvalidArgumentException('Параметры "user_id" и "key" обязательны.');
        }

        // Проверка на наличие обязательных данных
        $hasLoginAndPassword = !empty($data['login'] ?? null) && !empty($data['password'] ?? null);
        $hasApiKeys = !empty($data['public_api_key'] ?? null);

        if (!$hasLoginAndPassword && !$hasApiKeys) {
            throw new InvalidArgumentException(
                'Необходимо указать либо "login" и "password", либо "public_api_key".',
            );
        }

        // Подготовка значения JSON
        $value = [
            'login' => $data['login'] ?? '', // Если ключ отсутствует, устанавливаем пустую строку
            'password' => $data['password'] ?? '',
            'public_api_key' => $data['public_api_key'] ?? '',
            'private_api_key' => $data['private_api_key'] ?? '',
        ];

        // Создание записи (value обрабатывается автоматически через трейт)
        return self::create([
            'user_id' => $data['user_id'],
            'key' => $data['key'],
            'value' => $value,
            'type' => $data['type'] ?? null,
        ]);
    }

    /**
     * @return \Common\Models\Traits\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
