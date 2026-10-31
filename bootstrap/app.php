<?php

use App\Http\Middleware\EnsureTenantMember;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\IdentifyTenant;
use GarAge\Customers\Domain\CustomerTaxIdAlreadyUsed;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Shared\Domain\NotFound;
use GarAge\Units\Domain\UnitCodeAlreadyUsed;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'tenant' => IdentifyTenant::class,
            'tenant.member' => EnsureTenantMember::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Translate domain errors into HTTP responses (the domain knows nothing about HTTP).
        $exceptions->map(NotFound::class, fn (NotFound $e) => new NotFoundHttpException($e->getMessage(), $e));
        $exceptions->map(UnitCodeAlreadyUsed::class, fn () => ValidationException::withMessages([
            'code' => 'Ya existe una unidad con ese código.',
        ]));
        $exceptions->map(CustomerTaxIdAlreadyUsed::class, fn () => ValidationException::withMessages([
            'tax_id' => 'Ya existe un cliente con ese DNI, NIE o CIF.',
        ]));
        $exceptions->map(InvalidValue::class, fn (InvalidValue $e) => new UnprocessableEntityHttpException($e->getMessage(), $e));
    })->create();
