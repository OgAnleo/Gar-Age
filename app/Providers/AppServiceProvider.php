<?php

namespace App\Providers;

use App\Tenancy\CurrentTenant;
use Carbon\CarbonImmutable;
use GarAge\Tenancy\Application\ResolveTenant;
use GarAge\Tenancy\Domain\Tenant;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
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
