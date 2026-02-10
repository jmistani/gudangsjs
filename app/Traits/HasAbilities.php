<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Spatie\Ability\Contracts\Ability;
use Spatie\Ability\Exceptions\GuardDoesNotMatch;
use Spatie\Ability\Exceptions\AbilityDoesNotExist;
use Spatie\Ability\Exceptions\WildcardAbilityInvalidArgument;
use Spatie\Ability\Guard;
use Spatie\Ability\AbilityRegistrar;
use Spatie\Ability\WildcardAbility;

trait HasAbilities
{
    private $permissionClass;

    public static function bootHasAbilities()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->permissions()->detach();
        });
    }

    public function getAbilityClass()
    {
        if (! isset($this->permissionClass)) {
            $this->permissionClass = app(AbilityRegistrar::class)->getAbilityClass();
        }

        return $this->permissionClass;
    }

    /**
     * A model may have multiple direct permissions.
     */
    public function ability()
    {
        $permissions = $this->morphToMany(
            config('permission.models.permission'),
            'model',
            config('permission.table_names.model_has_permissions'),
            config('permission.column_names.model_morph_key'),
            'permission_id'
        );

        $abilities = array();
        foreach($permissions as $perm)
        {
            $str = explode(" ",$perm);
            if(count($str) == 2)
            {
                $ability = ["action" => $str[0], "subject" => $str[1]];
                array_push($ability,$abilities);
        
            }
        }
        return $abilities;
    }

    /**
     * Scope the model query to certain permissions only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array|\Spatie\Ability\Contracts\Ability|\Illuminate\Support\Collection $permissions
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAbility(Builder $query, $permissions): Builder
    {
        $permissions = $this->convertToAbilityModels($permissions);

        $rolesWithAbilitys = array_unique(array_reduce($permissions, function ($result, $permission) {
            return array_merge($result, $permission->roles->all());
        }, []));

        return $query->where(function (Builder $query) use ($permissions, $rolesWithAbilitys) {
            $query->whereHas('permissions', function (Builder $subQuery) use ($permissions) {
                $subQuery->whereIn(config('permission.table_names.permissions').'.id', \array_column($permissions, 'id'));
            });
            if (count($rolesWithAbilitys) > 0) {
                $query->orWhereHas('roles', function (Builder $subQuery) use ($rolesWithAbilitys) {
                    $subQuery->whereIn(config('permission.table_names.roles').'.id', \array_column($rolesWithAbilitys, 'id'));
                });
            }
        });
    }

    /**
     * @param string|array|\Spatie\Ability\Contracts\Ability|\Illuminate\Support\Collection $permissions
     *
     * @return array
     */
    protected function convertToAbilityModels($permissions): array
    {
        if ($permissions instanceof Collection) {
            $permissions = $permissions->all();
        }

        $permissions = is_array($permissions) ? $permissions : [$permissions];

        return array_map(function ($permission) {
            if ($permission instanceof Ability) {
                return $permission;
            }

            return $this->getAbilityClass()->findByName($permission, $this->getDefaultGuardName());
        }, $permissions);
    }

    /**
     * Determine if the model may perform the given permission.
     *
     * @param string|int|\Spatie\Ability\Contracts\Ability $permission
     * @param string|null $guardName
     *
     * @return bool
     * @throws AbilityDoesNotExist
     */
    public function hasAbilityTo($permission, $guardName = null): bool
    {
        if (config('permission.enable_wildcard_permission', false)) {
            return $this->hasWildcardAbility($permission, $guardName);
        }

        $permissionClass = $this->getAbilityClass();

        if (is_string($permission)) {
            $permission = $permissionClass->findByName(
                $permission,
                $guardName ?? $this->getDefaultGuardName()
            );
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById(
                $permission,
                $guardName ?? $this->getDefaultGuardName()
            );
        }

        if (! $permission instanceof Ability) {
            throw new AbilityDoesNotExist;
        }

        return $this->hasDirectAbility($permission) || $this->hasAbilityViaRole($permission);
    }

    /**
     * Validates a wildcard permission against all permissions of a user.
     *
     * @param string|int|\Spatie\Ability\Contracts\Ability $permission
     * @param string|null $guardName
     *
     * @return bool
     */
    protected function hasWildcardAbility($permission, $guardName = null): bool
    {
        $guardName = $guardName ?? $this->getDefaultGuardName();

        if (is_int($permission)) {
            $permission = $this->getAbilityClass()->findById($permission, $guardName);
        }

        if ($permission instanceof Ability) {
            $permission = $permission->name;
        }

        if (! is_string($permission)) {
            throw WildcardAbilityInvalidArgument::create();
        }

        foreach ($this->getAllAbilities() as $userAbility) {
            if ($guardName !== $userAbility->guard_name) {
                continue;
            }

            $userAbility = new WildcardAbility($userAbility->name);

            if ($userAbility->implies($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @deprecated since 2.35.0
     * @alias of hasAbilityTo()
     */
    public function hasUncachedAbilityTo($permission, $guardName = null): bool
    {
        return $this->hasAbilityTo($permission, $guardName);
    }

    /**
     * An alias to hasAbilityTo(), but avoids throwing an exception.
     *
     * @param string|int|\Spatie\Ability\Contracts\Ability $permission
     * @param string|null $guardName
     *
     * @return bool
     */
    public function checkAbilityTo($permission, $guardName = null): bool
    {
        try {
            return $this->hasAbilityTo($permission, $guardName);
        } catch (AbilityDoesNotExist $e) {
            return false;
        }
    }

    /**
     * Determine if the model has any of the given permissions.
     *
     * @param array ...$permissions
     *
     * @return bool
     * @throws \Exception
     */
    public function hasAnyAbility(...$permissions): bool
    {
        $permissions = collect($permissions)->flatten();

        foreach ($permissions as $permission) {
            if ($this->checkAbilityTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the model has all of the given permissions.
     *
     * @param array ...$permissions
     *
     * @return bool
     * @throws \Exception
     */
    public function hasAllAbilitys(...$permissions): bool
    {
        $permissions = collect($permissions)->flatten();

        foreach ($permissions as $permission) {
            if (! $this->hasAbilityTo($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the model has, via roles, the given permission.
     *
     * @param \Spatie\Ability\Contracts\Ability $permission
     *
     * @return bool
     */
    protected function hasAbilityViaRole(Ability $permission): bool
    {
        return $this->hasRole($permission->roles);
    }

    /**
     * Determine if the model has the given permission.
     *
     * @param string|int|\Spatie\Ability\Contracts\Ability $permission
     *
     * @return bool
     * @throws AbilityDoesNotExist
     */
    public function hasDirectAbility($permission): bool
    {
        $permissionClass = $this->getAbilityClass();

        if (is_string($permission)) {
            $permission = $permissionClass->findByName($permission, $this->getDefaultGuardName());
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById($permission, $this->getDefaultGuardName());
        }

        if (! $permission instanceof Ability) {
            throw new AbilityDoesNotExist;
        }

        return $this->permissions->contains('id', $permission->id);
    }

    /**
     * Return all the permissions the model has via roles.
     */
    public function getAbilitysViaRoles(): Collection
    {
        return $this->loadMissing('roles', 'roles.permissions')
            ->roles->flatMap(function ($role) {
                return $role->permissions;
            })->sort()->values();
    }

    /**
     * Return all the permissions the model has, both directly and via roles.
     */
    public function getAllAbilities(): Collection
    {
        /** @var Collection $permissions */
        $permissions = $this->permissions;

        if ($this->roles) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }

        return $permissions->sort()->values();
    }

    /**
     * Grant the given permission(s) to a role.
     *
     * @param string|array|\Spatie\Ability\Contracts\Ability|\Illuminate\Support\Collection $permissions
     *
     * @return $this
     */
    public function giveAbilityTo(...$permissions)
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                if (empty($permission)) {
                    return false;
                }

                return $this->getStoredAbility($permission);
            })
            ->filter(function ($permission) {
                return $permission instanceof Ability;
            })
            ->each(function ($permission) {
                $this->ensureModelSharesGuard($permission);
            })
            ->map->id
            ->all();

        $model = $this->getModel();

        if ($model->exists) {
            $this->permissions()->sync($permissions, false);
            $model->load('permissions');
        } else {
            $class = \get_class($model);

            $class::saved(
                function ($object) use ($permissions, $model) {
                    $model->permissions()->sync($permissions, false);
                    $model->load('permissions');
                }
            );
        }

        $this->forgetCachedAbilitys();

        return $this;
    }

    /**
     * Remove all current permissions and set the given ones.
     *
     * @param string|array|\Spatie\Ability\Contracts\Ability|\Illuminate\Support\Collection $permissions
     *
     * @return $this
     */
    public function syncAbilitys(...$permissions)
    {
        $this->permissions()->detach();

        return $this->giveAbilityTo($permissions);
    }

    /**
     * Revoke the given permission.
     *
     * @param \Spatie\Ability\Contracts\Ability|\Spatie\Ability\Contracts\Ability[]|string|string[] $permission
     *
     * @return $this
     */
    public function revokeAbilityTo($permission)
    {
        $this->permissions()->detach($this->getStoredAbility($permission));

        $this->forgetCachedAbilitys();

        $this->load('permissions');

        return $this;
    }

    public function getAbilityNames(): Collection
    {
        return $this->permissions->pluck('name');
    }

    /**
     * @param string|array|\Spatie\Ability\Contracts\Ability|\Illuminate\Support\Collection $permissions
     *
     * @return \Spatie\Ability\Contracts\Ability|\Spatie\Ability\Contracts\Ability[]|\Illuminate\Support\Collection
     */
    protected function getStoredAbility($permissions)
    {
        $permissionClass = $this->getAbilityClass();

        if (is_numeric($permissions)) {
            return $permissionClass->findById($permissions, $this->getDefaultGuardName());
        }

        if (is_string($permissions)) {
            return $permissionClass->findByName($permissions, $this->getDefaultGuardName());
        }

        if (is_array($permissions)) {
            return $permissionClass
                ->whereIn('name', $permissions)
                ->whereIn('guard_name', $this->getGuardNames())
                ->get();
        }

        return $permissions;
    }

    // /**
    //  * @param \Spatie\Ability\Contracts\Ability|\Spatie\Ability\Contracts\Role $roleOrAbility
    //  *
    //  * @throws \Spatie\Ability\Exceptions\GuardDoesNotMatch
    //  */
    // protected function ensureModelSharesGuard($roleOrAbility)
    // {
    //     if (! $this->getGuardNames()->contains($roleOrAbility->guard_name)) {
    //         throw GuardDoesNotMatch::create($roleOrAbility->guard_name, $this->getGuardNames());
    //     }
    // }

    // protected function getGuardNames(): Collection
    // {
    //     return Guard::getNames($this);
    // }

    // protected function getDefaultGuardName(): string
    // {
    //     return Guard::getDefaultName($this);
    // }

    // /**
    //  * Forget the cached permissions.
    //  */
    // public function forgetCachedAbilitys()
    // {
    //     app(AbilityRegistrar::class)->forgetCachedAbilitys();
    // }

    /**
     * Check if the model has All of the requested Direct permissions.
     * @param array ...$permissions
     * @return bool
     */
    public function hasAllDirectAbilitys(...$permissions): bool
    {
        $permissions = collect($permissions)->flatten();

        foreach ($permissions as $permission) {
            if (! $this->hasDirectAbility($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the model has Any of the requested Direct permissions.
     * @param array ...$permissions
     * @return bool
     */
    public function hasAnyDirectAbility(...$permissions): bool
    {
        $permissions = collect($permissions)->flatten();

        foreach ($permissions as $permission) {
            if ($this->hasDirectAbility($permission)) {
                return true;
            }
        }

        return false;
    }
}
