<?php

use App\Models\Contract;
use App\Models\Fee;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-10'));
    config(['services.cron.token' => 'secret-token']);
    Contract::factory()->create(['starts_on' => '2026-10-01']);
});

it('issues the due fees of every business for the external scheduler, once', function () {
    $this->withToken('secret-token')
        ->postJson('/cron/fees')
        ->assertOk()
        ->assertExactJson(['date' => '2026-11-10', 'issued' => 2]);

    $this->withToken('secret-token')
        ->postJson('/cron/fees')
        ->assertExactJson(['date' => '2026-11-10', 'issued' => 0]);

    expect(Fee::withoutGlobalScopes()->count())->toBe(2);
});

it('rejects calls without the right token', function (?string $token) {
    $request = $token === null ? $this : $this->withToken($token);

    $request->postJson('/cron/fees')->assertUnauthorized();

    expect(Fee::withoutGlobalScopes()->count())->toBe(0);
})->with(['no token' => [null], 'wrong token' => ['guess']]);

it('does not exist when no token is configured', function () {
    config(['services.cron.token' => null]);

    $this->withToken('')->postJson('/cron/fees')->assertNotFound();
});

it('skips the CSRF check, which tests never run', function () {
    expect(Route::getRoutes()->getByName('cron.fees')?->excludedMiddleware())->toContain(PreventRequestForgery::class)
        ->and(Route::getRoutes()->getByName('cron.demo')?->excludedMiddleware())->toContain(PreventRequestForgery::class);
});
