<?php

namespace App\Http\Middleware;

use App\Tenancy\CurrentTenant;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'tenant' => fn () => $this->tenantProps($request),
            'demo' => fn () => $this->demoProps($request),
        ];
    }

    /**
     * The published demo credentials, only on the public demo.
     *
     * @return array{email: string, password: string, isDemoUser: bool}|null
     */
    private function demoProps(Request $request): ?array
    {
        return config('demo.enabled') !== true ? null : [
            'email' => (string) config('demo.email'),
            'password' => (string) config('demo.password'),
            'isDemoUser' => ProtectDemoAccount::isDemoUser($request),
        ];
    }

    /**
     * The current tenant, and whether the user may manage it (owners) or only run it (staff).
     *
     * @return array{id: int, name: string, slug: string, canManage: bool}|null
     */
    private function tenantProps(Request $request): ?array
    {
        $tenant = app(CurrentTenant::class)->get();

        return $tenant === null ? null : [
            'id' => $tenant->id->value,
            'name' => $tenant->name,
            'slug' => $tenant->slug->value,
            'canManage' => $request->user()?->can('manage-business') ?? false,
        ];
    }
}
