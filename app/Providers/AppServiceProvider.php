<?php

namespace App\Providers;

use App\Http\Responses\PublicLogoutResponse;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Policies\AssetPolicy;
use App\Policies\AuditLogPolicy;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LogoutResponse::class, PublicLogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Asset::class, AssetPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);

        foreach (['assets.view', 'assets.create', 'assets.update', 'users.manage', 'structure.manage', 'people.manage', 'catalogs.manage', 'audit.view'] as $permission) {
            Gate::define($permission, fn ($user) => $user->hasPermission($permission));
        }
    }
}
