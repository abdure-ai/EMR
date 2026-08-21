<?php

namespace App\Providers;

use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super Admin bypasses every check, EXCEPT modifying the Super Admin
        // role itself - otherwise this blanket bypass would short-circuit
        // RolePolicy's guard against self-lockout before it ever runs.
        Gate::before(function ($user, string $ability, array $arguments = []) {
            if (! $user->hasRole('Super Admin')) {
                return null;
            }

            $target = $arguments[0] ?? null;

            if ($target instanceof Role && $target->name === 'Super Admin' && in_array($ability, ['update', 'delete'], true)) {
                return null;
            }

            return true;
        });

        // Spatie's Role/Permission models live outside App\Models, so Laravel's
        // convention-based policy auto-discovery won't find these on its own.
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
    }
}
