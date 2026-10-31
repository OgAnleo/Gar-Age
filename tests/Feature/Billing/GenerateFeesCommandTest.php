<?php

use App\Models\Contract;
use App\Models\Fee;
use App\Models\Tenant;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;

it('issues the fees of every tenant for the periods started by the day, once', function () {
    $monthly = Contract::factory()->create(['mode' => 'monthly', 'starts_on' => '2026-09-15', 'price_per_period_cents' => 7999]);
    Contract::factory()->onDays(['2026-10-20', '2026-10-28'])->create(['price_per_period_cents' => 600]);
    Contract::factory()->create(['starts_on' => '2026-11-01']); // not started yet

    $this->artisan('fees:generate', ['date' => '2026-10-20'])
        ->expectsOutput('3 fee(s) issued up to 2026-10-20.')
        ->assertSuccessful();

    $this->artisan('fees:generate', ['date' => '2026-10-20'])
        ->expectsOutput('0 fee(s) issued up to 2026-10-20.');

    $fees = Fee::where('contract_id', $monthly->id)->orderBy('period_start')->get();

    expect(Fee::count())->toBe(3)
        ->and($fees->pluck('period_start')->all())->toBe(['2026-09-15', '2026-10-15'])
        ->and($fees->first()->period_end)->toBe('2026-10-14')
        ->and($fees->first()->tenant_id)->toBe($monthly->tenant_id)
        ->and($fees->first()->amount_cents)->toBe(7999);
});

it('defaults to today', function () {
    $this->travelTo(Carbon::parse('2026-12-10'));
    Contract::factory()->create(['mode' => 'annual', 'starts_on' => '2026-01-01']);

    $this->artisan('fees:generate')->expectsOutput('1 fee(s) issued up to 2026-12-10.');
});

it('rejects a malformed date', function () {
    $this->artisan('fees:generate', ['date' => 'mañana'])
        ->expectsOutput('The date must be written as YYYY-MM-DD.')
        ->assertExitCode(2);
});

it('is scheduled every day', function () {
    $events = collect(app(Schedule::class)->events())
        ->filter(fn ($event) => str_contains((string) $event->command, 'fees:generate'));

    expect($events)->toHaveCount(1)
        ->and($events->first()->expression)->toBe('0 3 * * *');
});

it('assigns each fee to the tenant of its contract', function () {
    $tenant = Tenant::factory()->create();
    Contract::factory()->for($tenant)->create(['mode' => 'weekly', 'starts_on' => '2026-10-01', 'period_count' => 1]);

    $this->artisan('fees:generate', ['date' => '2026-10-31']);

    expect($tenant->fees()->count())->toBe(1);
});
