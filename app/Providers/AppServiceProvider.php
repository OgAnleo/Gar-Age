<?php

namespace App\Providers;

use App\Models\User;
use App\Tenancy\CurrentTenant;
use Carbon\CarbonImmutable;
use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Application\GetMemberRole;
use GarAge\Tenancy\Application\ResolveTenant;
use GarAge\Tenancy\Domain\Role;
use GarAge\Tenancy\Domain\Tenant;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(CurrentTenant::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureTenantRouteBinding();
        $this->configureGates();
    }

    /**
     * Owners manage the business (pricing, team, public page, deletion);
     * staff only run its day to day. Used as `can:manage-business`.
     */
    protected function configureGates(): void
    {
        Gate::define('manage-business', fn (User $user): bool => ($tenant = $this->app->make(CurrentTenant::class)->get()) !== null
            && $this->app->make(GetMemberRole::class)->handle($tenant->id, new UserId($user->id)) === Role::Owner);
    }

    /**
     * Resolve the {tenant} route parameter by slug (404 if it doesn't exist).
     */
    protected function configureTenantRouteBinding(): void
    {
        Route::bind('tenant', fn (string $value): Tenant => $this->app->make(ResolveTenant::class)->handle($value));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
