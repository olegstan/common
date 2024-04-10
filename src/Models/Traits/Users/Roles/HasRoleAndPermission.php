<?php

namespace Common\Models\Traits\Users\Roles;

use Cache;
use Common\Models\Users\Roles\Permission;
use Common\Models\Users\Roles\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use InvalidArgumentException;

trait HasRoleAndPermission
{
    /**
     * Свойство для кэширования ролей.
     *
     * @var Collection|null
     */
    protected ?Collection $roles;

    /**
     * Свойство для разрешений кэширования.
     *
     * @var Collection|null
     */
    protected ?Collection $permissions;

    /**
     * Пользователь принадлежит ко многим ролям.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(config('roles.models.role'))->withTimestamps();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getRoles(): \Illuminate\Support\Collection
    {
        $collect = collect();
        $roleSlugs = Cache::rememberForever('user.roles.' . $this->id, function ()
        {
            $roles = $this->roles ?? $this->roles()->get();
            $roleSlugs = [];

            if($roles)
            {
                foreach ($roles as $role)
                {
                    $roleSlugs[] = [
                        'id' => $role->id,
                        'name' => $role->name,
                        'slug' => $role->slug,
                        'level' => $role->level,
                        'description' => $role->description,
                    ];
                }
            }

            return $roleSlugs;
        });

        if($roleSlugs)
        {
            foreach ($roleSlugs as $slug)
            {
                $collect->push(new Role([
                    'id' => $slug['id'],
                    'name' => $slug['name'],
                    'slug' => $slug['slug'],
                    'level' => $slug['level'],
                    'description' => $slug['description'],
                ]));
            }
        }

        return $collect;
    }

    /**
     * Проверьте, есть ли у пользователя роль или роли ролевых функций.
     *
     * @param int|string|array $role
     * @param bool $all
     * @return bool
     */
    public function is($role, bool $all = false): bool
    {
        if ($this->isPretendEnabled()) {
            return $this->pretend('is');
        }

        return $this->{$this->getMethodName('is', $all)}($role);
    }

    /**
     * Проверьте, есть ли у пользователя хотя бы одна роль.
     *
     * @param int|string|array $role
     * @return bool
     */
    public function isOne($role): bool
    {
        foreach ($this->getArrayFrom($role) as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверьте, есть ли у пользователя все роли.
     *
     * @param int|string|array $role
     * @return bool
     */
    public function isAll($role): bool
    {
        foreach ($this->getArrayFrom($role) as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Проверьте, есть ли у пользователя роль.
     *
     * @param int|string $role
     * @return bool
     */
    public function hasRole($role): bool
    {
        return $this->getRoles()->contains(function ($key, $value) use ($role) {
            return $role == $value->id || Str::is($role, $value->slug);
        });
    }

    /**
     * Прикрепить роль к пользователю.
     *
     * @param int|Role $role
     * @return null|bool
     */
    public function attachRole($role): ?bool
    {
        return (!$this->roles()->get()->contains($role)) ? $this->roles()->attach($role) : true;
    }

    /**
     * Отсоединить роль от пользователя.
     *
     * @param int|Role $role
     * @return int
     */
    public function detachRole($role): int
    {
        $this->roles = null;

        return $this->roles()->detach($role);
    }

    /**
     * Отсоединить все роли от пользователя.
     *
     * @return int
     */
    public function detachAllRoles(): int
    {
        $this->roles = null;

        return $this->roles()->detach();
    }

    /**
     * Получить уровень роли пользователя.
     *
     * @return int
     */
    public function level(): int
    {
        return ($role = $this->getRoles()->sortByDesc('level')->first()) ? $role->level : 0;
    }

    /**
     * Получить все разрешения от ролей.
     *
     * @return Builder
     */
    public function rolePermissions(): Builder
    {
        $permissionModel = app(config('roles.models.permission'));

        if (!($permissionModel instanceof Model)) {
            throw new InvalidArgumentException('[roles.models.permission] must be an instance of \Illuminate\Database\Eloquent\Model');
        }

        return $permissionModel::select(['permissions.*', 'permission_role.created_at as pivot_created_at', 'permission_role.updated_at as pivot_updated_at'])
                ->join('permission_role', 'permission_role.permission_id', '=', 'permissions.id')->join('roles', 'roles.id', '=', 'permission_role.role_id')
                ->whereIn('roles.id', $this->getRoles()->pluck('id')->toArray())->orWhere('roles.level', '<', $this->level())
                ->groupBy(['permissions.id', 'pivot_created_at', 'pivot_updated_at']);
    }

    /**
     * Пользователь обладает многими разрешениями.
     *
     * @return BelongsToMany
     */
    public function userPermissions(): BelongsToMany
    {
        return $this->belongsToMany(config('roles.models.permission'))->withTimestamps();
    }

    /**
     * Получите все разрешения в виде коллекции.
     *
     * @return Collection
     */
    public function getPermissions(): Collection
    {
        return (!isset($this->permissions)) ? $this->permissions = $this->rolePermissions()->get()->merge($this->userPermissions()->get()) : $this->permissions;
    }

    /**
     * Проверьте, есть ли у пользователя разрешение или permissions.
     *
     * @param int|string|array $permission
     * @param bool $all
     * @return bool
     */
    public function can($permission, bool $all = false): bool
    {
        if ($this->isPretendEnabled()) {
            return $this->pretend('can');
        }

        return $this->{$this->getMethodName('can', $all)}($permission);
    }

    /**
     * Проверьте, есть ли у пользователя хотя бы одно разрешение.
     *
     * @param int|string|array $permission
     * @return bool
     */
    public function canOne($permission): bool
    {
        foreach ($this->getArrayFrom($permission) as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверьте, есть ли у пользователя все разрешения.
     *
     * @param int|string|array $permission
     * @return bool
     */
    public function canAll($permission): bool
    {
        foreach ($this->getArrayFrom($permission) as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Проверьте, есть ли у пользователя разрешение.
     *
     * @param int|string $permission
     * @return bool
     */
    public function hasPermission($permission): bool
    {
        return $this->getPermissions()->contains(function ($key, $value) use ($permission) {
            return $permission == $value->id || Str::is($permission, $value->slug);
        });
    }

    /**
     * Проверьте, разрешено ли пользователю манипулировать с entity.
     *
     * @param string $providedPermission
     * @param Model $entity
     * @param bool $owner
     * @param string $ownerColumn
     * @return bool
     */
    public function allowed(string $providedPermission, Model $entity, bool $owner = true, string $ownerColumn = 'user_id'): bool
    {
        if ($this->isPretendEnabled()) {
            return $this->pretend('allowed');
        }

        if ($owner === true && $entity->{$ownerColumn} == $this->id) {
            return true;
        }

        return $this->isAllowed($providedPermission, $entity);
    }

    /**
     * Проверьте, разрешено ли пользователю манипулировать предоставленной сущностью.
     *
     * @param string $providedPermission
     * @param Model $entity
     * @return bool
     */
    protected function isAllowed(string $providedPermission, Model $entity): bool
    {
        foreach ($this->getPermissions() as $permission) {
            if (($permission->id == $providedPermission || $permission->slug === $providedPermission)
                && $permission->model != '' && get_class($entity) == $permission->model
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Прикрепите разрешение к пользователю.
     *
     * @param int|Permission $permission
     * @return null|bool
     */
    public function attachPermission($permission): ?bool
    {
        return (!$this->getPermissions()->contains($permission)) ? $this->userPermissions()->attach($permission) : true;
    }

    /**
     * Отсоедините разрешение от пользователя.
     *
     * @param int|Permission $permission
     * @return int
     */
    public function detachPermission($permission): int
    {
        $this->permissions = null;

        return $this->userPermissions()->detach($permission);
    }

    /**
     * Отсоедините все разрешения от пользователя.
     *
     * @return int
     */
    public function detachAllPermissions(): int
    {
        $this->permissions = null;
        
        return $this->userPermissions()->detach();
    }

    /**
     * Проверьте, включена ли опция притворяться.
     *
     * @return bool
     */
    private function isPretendEnabled(): bool
    {
        return (bool) config('roles.pretend.enabled');
    }

    /**
     * Позволяет притворяться или имитировать поведение пакета.
     *
     * @param string $option
     * @return bool
     */
    private function pretend(string $option): bool
    {
        return (bool) config('roles.pretend.options.' . $option);
    }

    /**
     * Получить имя метода.
     *
     * @param string $methodName
     * @param bool $all
     * @return string
     */
    private function getMethodName(string $methodName, bool $all): string
    {
        return ((bool) $all) ? $methodName . 'All' : $methodName . 'One';
    }

    /**
     * Получаем массив из аргумента.
     *
     * @param int|string|array $argument
     * @return array
     */
    private function getArrayFrom($argument): array
    {
        return (!is_array($argument)) ? preg_split('/ ?[,|] ?/', $argument) : $argument;
    }

    /**
     * Обрабатываем вызовы динамических методов.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (starts_with($method, 'is')) {
            return $this->is(snake_case(substr($method, 2), config('roles.separator')));
        }

        if (starts_with($method, 'can')) {
            return $this->can(snake_case(substr($method, 3), config('roles.separator')));
        }

        if (starts_with($method, 'allowed')) {
            return $this->allowed(snake_case(substr($method, 7), config('roles.separator')), $parameters[0],
                $parameters[1] ?? true,
                $parameters[2] ?? 'user_id'
            );
        }

        return parent::__call($method, $parameters);
    }
}
