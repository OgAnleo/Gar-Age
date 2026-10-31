<?php

use GarAge\Billing\Application\GenerateDueFees;
use GarAge\Billing\Application\GetFee;
use GarAge\Billing\Application\ListFees;
use GarAge\Billing\Application\MarkFeePaid;
use GarAge\Billing\Application\MarkFeeUnpaid;
use GarAge\Billing\Domain\Fee;
use GarAge\Billing\Domain\FeeNotFound;
use GarAge\Billing\Infrastructure\FeesContractUsage;
use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractDetails;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\RecurringSchedule;
use GarAge\Contracts\Domain\Schedule;
use GarAge\Contracts\Domain\SpecificDaysSchedule;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Pricing\Domain\BookingMode;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\Money;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitId;
use Tests\Doubles\InMemoryContractRepository;
use Tests\Doubles\InMemoryFeeRepository;

beforeEach(function () {
    $this->tenantA = new TenantId(1);
    $this->tenantB = new TenantId(2);

    $contracts = new InMemoryContractRepository;
    $contract = fn (TenantId $tenant, Schedule $schedule, string $price) => $contracts->add(Contract::create(
        $tenant,
        new ContractDetails(new UnitId(1), new CustomerId(1), $schedule, Money::fromString($price)),
    ));
    $this->weekly = $contract($this->tenantA, new RecurringSchedule(BookingMode::Weekly, DateRange::day('2026-10-01'), 2), '12.60');
    $contract($this->tenantA, SpecificDaysSchedule::fromStrings(['2026-10-20', '2026-10-25']), '4');
    $contract($this->tenantA, new RecurringSchedule(BookingMode::Monthly, DateRange::day('2026-11-01')), '60'); // not started
    $contract($this->tenantB, new RecurringSchedule(BookingMode::Annual, DateRange::day('2026-01-15')), '511');

    $this->fees = new InMemoryFeeRepository;
    $getFee = new GetFee($this->fees);

    $this->generate = new GenerateDueFees($contracts, $this->fees);
    $this->list = new ListFees($this->fees);
    $this->pay = new MarkFeePaid($getFee, $this->fees);
    $this->unpay = new MarkFeeUnpaid($getFee, $this->fees);
    $this->today = new DateTimeImmutable('2026-10-31');
});

it('issues one fee per billing period started, at the agreed price', function () {
    expect($this->generate->handle($this->today))->toBe(4);

    $fees = array_map(
        fn (Fee $fee) => "{$fee->period->startString()}..{$fee->period->endString()} {$fee->amount->cents()}",
        $this->list->handle($this->tenantA),
    );

    expect($fees)->toBe([
        '2026-10-20..2026-10-25 400',  // specific days: one fee for all of them
        '2026-10-08..2026-10-14 1260', // second and last week
        '2026-10-01..2026-10-07 1260',
    ])->and($this->list->handle($this->tenantB))->toHaveCount(1); // first year
});

it('can be run again without issuing duplicates', function () {
    $this->generate->handle($this->today);

    expect($this->generate->handle($this->today))->toBe(0)
        ->and($this->generate->handle(new DateTimeImmutable('2026-11-01')))->toBe(1); // the monthly contract starts
});

it('can be limited to one tenant', function () {
    expect($this->generate->handle($this->today, $this->tenantB))->toBe(1)
        ->and($this->list->handle($this->tenantA))->toBe([]);
});

it('marks fees paid and unpaid, hiding other tenants', function () {
    $this->generate->handle($this->today);
    $fee = $this->list->handle($this->tenantA)[0];

    $this->pay->handle($this->tenantA, $fee->id(), new DateTimeImmutable('2026-10-05'));
    expect($fee->paidOn()?->format('Y-m-d'))->toBe('2026-10-05');

    $this->unpay->handle($this->tenantA, $fee->id());
    expect($fee->paidOn())->toBeNull()
        ->and(fn () => $this->pay->handle($this->tenantB, $fee->id(), new DateTimeImmutable))->toThrow(FeeNotFound::class);
});

it('reports contracts with fees as in use', function () {
    $usage = new FeesContractUsage($this->fees);
    expect($usage->isInUse($this->tenantA, $this->weekly))->toBeFalse();

    $this->generate->handle($this->today);

    expect($usage->isInUse($this->tenantA, $this->weekly))->toBeTrue()
        ->and($usage->isInUse($this->tenantB, $this->weekly))->toBeFalse()
        ->and($usage->isInUse($this->tenantA, new ContractId(99)))->toBeFalse();
});
