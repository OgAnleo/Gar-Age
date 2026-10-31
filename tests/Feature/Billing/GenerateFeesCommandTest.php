<?php

use App\Models\Contract;
use App\Models\Fee;
use App\Models\Tenant;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;

it('issues the fees of every tenant for the given month, once', function () {
    $running = Contract::factory()->create(['starts_on' => '2026-01-01', 'ends_on' => null, 'monthly_price_cents' => 7999]);
    Contract::factory()->create(['starts_on' => '2026-09-15', 'ends_on' => '2026-10-02']);
    Contract::factory()->create(['starts_on' => '2026-11-01']); // not running in October

    $this->artisan('fees:generate', ['month' => '2026-10'])
        ->expectsOutput('2 fee(s) issued for 2026-10.')
        ->assertSuccessful();

    $this->artisan('fees:generate', ['month' => '2026-10'])
        ->expectsOutput('0 fee(s) issued for 2026-10.');

    $fee = Fee::where('contract_id', $running->id)->sole();

    expect(Fee::count())->toBe(2)
        ->and($fee->tenant_id)->toBe($running->tenant_id)
        ->and($fee->amount_cents)->toBe(7999)
        ->and($fee->paid_on)->toBeNull();
});

it('defaults to the current month', function () {
    $this->travelTo(Carbon::parse('2026-12-10'));
    Contract::factory()->create(['starts_on' => '2026-01-01']);

    $this->artisan('fees:generate')->expectsOutput('1 fee(s) issued for 2026-12.');
});

it('rejects a malformed month', function () {
    $this->artisan('fees:generate', ['month' => 'octubre'])
        ->expectsOutput('The month must be written as YYYY-MM.')
        ->assertExitCode(2);
});

it('is scheduled every day', function () {
    $events = collect(app(Schedule::class)->events())
        ->filter(fn ($event) => str_contains((string) $event->command, 'fees:generate'));

    expect($events)->toHaveCount(1)
        ->and($events->first()->expression)->toBe('0 3 * * *');
});

it('keeps tenants apart when the same contract ids are queried', function () {
    $tenant = Tenant::factory()->create();
    Contract::factory()->for($tenant)->create(['starts_on' => '2026-10-01']);

    $this->artisan('fees:generate', ['month' => '2026-10']);

    expect($tenant->fees()->count())->toBe(1);
});
