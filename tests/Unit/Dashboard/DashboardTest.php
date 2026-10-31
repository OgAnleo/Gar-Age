<?php

use GarAge\Billing\Domain\Fee;
use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractDetails;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\RecurringSchedule;
use GarAge\Contracts\Domain\Schedule;
use GarAge\Contracts\Domain\SpecificDaysSchedule;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Dashboard\Application\GetDashboard;
use GarAge\Dashboard\Application\OverdueFee;
use GarAge\Dashboard\Domain\FeeTally;
use GarAge\Dashboard\Domain\Occupancy;
use GarAge\Pricing\Domain\BookingMode;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Shared\Domain\Money;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\Area;
use GarAge\Units\Domain\Unit;
use GarAge\Units\Domain\UnitCode;
use GarAge\Units\Domain\UnitDetails;
use GarAge\Units\Domain\UnitId;
use GarAge\Units\Domain\UnitType;
use Tests\Doubles\InMemoryContractRepository;
use Tests\Doubles\InMemoryFeeRepository;
use Tests\Doubles\InMemoryUnitRepository;

beforeEach(function () {
    $tenantA = new TenantId(1);
    $tenantB = new TenantId(2);
    $this->tenantA = $tenantA;

    $units = new InMemoryUnitRepository;
    $unit = fn (TenantId $tenant, string $code): UnitId => $units->add(Unit::create(
        $tenant,
        new UnitDetails(new UnitCode($code), UnitType::Garage, Area::fromString('10'), Money::fromString('3')),
    ));
    $g01 = $unit($tenantA, 'G-01');
    $g02 = $unit($tenantA, 'G-02');
    $g03 = $unit($tenantA, 'G-03');
    $unit($tenantA, 'T-04');
    $b01 = $unit($tenantB, 'B-01');

    $contracts = new InMemoryContractRepository;
    $contract = fn (TenantId $tenant, UnitId $unitId, Schedule $schedule): ContractId => $contracts->add(Contract::create(
        $tenant,
        new ContractDetails($unitId, new CustomerId(1), $schedule, Money::fromString('1')),
    ));
    $this->monthly = $contract($tenantA, $g01, new RecurringSchedule(BookingMode::Monthly, DateRange::day('2026-09-01')));
    $days = $contract($tenantA, $g02, SpecificDaysSchedule::fromStrings(['2026-10-28', '2026-10-31']));
    $this->weekly = $contract($tenantA, $g03, new RecurringSchedule(BookingMode::Weekly, DateRange::day('2026-10-01'), 2)); // ended
    $other = $contract($tenantB, $b01, new RecurringSchedule(BookingMode::Monthly, DateRange::day('2026-10-01')));

    $fees = new InMemoryFeeRepository;
    $fee = function (TenantId $tenant, ContractId $contractId, string $start, string $end, string $amount, ?string $paidOn = null) use ($fees): void {
        $fee = Fee::issue($tenant, $contractId, DateRange::fromStrings($start, $end), Money::fromString($amount));
        if ($paidOn !== null) {
            $fee->markPaid(new DateTimeImmutable($paidOn));
        }
        $fees->add($fee);
    };
    $fee($tenantA, $this->monthly, '2026-04-01', '2026-04-30', '60', '2026-04-30'); // collected before the chart
    $fee($tenantA, $this->monthly, '2026-09-01', '2026-09-30', '60', '2026-09-05');
    $fee($tenantA, $this->monthly, '2026-10-01', '2026-10-31', '60');                 // overdue since 10-08
    $fee($tenantA, $this->weekly, '2026-10-01', '2026-10-07', '12.60', '2026-10-02');
    $fee($tenantA, $this->weekly, '2026-10-08', '2026-10-14', '12.60');               // overdue since 10-15
    $fee($tenantA, $days, '2026-10-28', '2026-10-31', '8');                           // pending until 11-04
    $fee($tenantB, $other, '2026-10-01', '2026-10-31', '100', '2026-10-01');

    $this->getDashboard = new GetDashboard($units, $contracts, $fees);
    $this->today = new DateTimeImmutable('2026-10-31 15:00');
});

it('measures today\'s occupancy and lists the free units', function () {
    $summary = $this->getDashboard->handle($this->tenantA, $this->today);

    expect($summary->occupancy->occupied)->toBe(2)
        ->and($summary->occupancy->total)->toBe(4)
        ->and($summary->occupancy->percent())->toBe(50)
        ->and(array_map(fn (Unit $unit): string => $unit->details()->code->value, $summary->freeUnits))->toBe(['G-03', 'T-04']);
});

it('adds up what was collected in the last months and what was issued this month', function () {
    $summary = $this->getDashboard->handle($this->tenantA, $this->today);

    expect(array_map(fn (Money $money): int => $money->cents(), $summary->collectedByMonth))->toBe([
        '2026-05' => 0,
        '2026-06' => 0,
        '2026-07' => 0,
        '2026-08' => 0,
        '2026-09' => 6000,
        '2026-10' => 1260,
    ])
        ->and($summary->collectedThisMonth()->cents())->toBe(1260)
        ->and($summary->issuedThisMonth->cents())->toBe(9320);
});

it('tallies pending and overdue fees, oldest overdue first', function () {
    $summary = $this->getDashboard->handle($this->tenantA, $this->today);

    expect([$summary->pending->count, $summary->pending->amount->cents()])->toBe([1, 800])
        ->and([$summary->overdue->count, $summary->overdue->amount->cents()])->toBe([2, 7260])
        ->and(array_map(
            fn (OverdueFee $overdue): array => [$overdue->fee->dueOn()->format('Y-m-d'), $overdue->contract?->id()->value],
            $summary->overdueFees,
        ))->toBe([['2026-10-08', $this->monthly->value], ['2026-10-15', $this->weekly->value]]);
});

it('shows nothing of other tenants', function () {
    $summary = $this->getDashboard->handle(new TenantId(3), $this->today);

    expect($summary->occupancy->total)->toBe(0)
        ->and($summary->occupancy->percent())->toBe(0)
        ->and($summary->collectedThisMonth()->cents())->toBe(0)
        ->and($summary->overdueFees)->toBe([]);
});

it('rounds the occupancy percentage half up', function (int $occupied, int $total, int $percent) {
    expect((new Occupancy($occupied, $total))->percent())->toBe($percent);
})->with([
    'a third' => [1, 3, 33],
    'two thirds' => [2, 3, 67],
    'an eighth' => [1, 8, 13],
    'full' => [5, 5, 100],
]);

it('rejects an impossible occupancy', function (int $occupied, int $total) {
    new Occupancy($occupied, $total);
})->with([[-1, 3], [4, 3]])->throws(InvalidValue::class);

it('tallies no fees as zero', function () {
    $tally = FeeTally::of([]);

    expect([$tally->count, $tally->amount->cents()])->toBe([0, 0]);
});
