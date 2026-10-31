<?php

use GarAge\Billing\Application\GenerateMonthlyFees;
use GarAge\Billing\Application\GetFee;
use GarAge\Billing\Application\ListFees;
use GarAge\Billing\Application\MarkFeePaid;
use GarAge\Billing\Application\MarkFeeUnpaid;
use GarAge\Billing\Domain\Fee;
use GarAge\Billing\Domain\FeeNotFound;
use GarAge\Billing\Domain\YearMonth;
use GarAge\Billing\Infrastructure\FeesContractUsage;
use GarAge\Contracts\Application\ContractInput;
use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Tenancy\Domain\TenantId;
use Tests\Doubles\InMemoryContractRepository;
use Tests\Doubles\InMemoryFeeRepository;

beforeEach(function () {
    $this->tenantA = new TenantId(1);
    $this->tenantB = new TenantId(2);

    $contracts = new InMemoryContractRepository;
    $contract = fn (TenantId $tenant, string $start, ?string $end, string $price = '80') => $contracts->add(
        Contract::create($tenant, (new ContractInput(1, 1, $start, $end, $price))->toDetails()),
    );
    $this->ending = $contract($this->tenantA, '2026-01-01', '2026-10-10', '95.50'); // ends during October
    $contract($this->tenantA, '2026-10-31', null);                                  // starts on its last day
    $contract($this->tenantA, '2026-11-01', null);                                  // starts later
    $contract($this->tenantB, '2025-01-01', null);                                  // another tenant

    $this->fees = new InMemoryFeeRepository;
    $getFee = new GetFee($this->fees);

    $this->generate = new GenerateMonthlyFees($contracts, $this->fees);
    $this->list = new ListFees($this->fees);
    $this->pay = new MarkFeePaid($getFee, $this->fees);
    $this->unpay = new MarkFeeUnpaid($getFee, $this->fees);
    $this->october = new YearMonth('2026-10');
});

it('issues one fee per contract running during the month, at its agreed price', function () {
    expect($this->generate->handle($this->october))->toBe(3);

    $amounts = array_map(fn (Fee $fee) => $fee->amount->cents(), $this->list->handle($this->tenantA));

    expect($amounts)->toEqualCanonicalizing([9550, 8000])
        ->and($this->list->handle($this->tenantB))->toHaveCount(1);
});

it('can be run again without issuing duplicates', function () {
    $this->generate->handle($this->october);

    expect($this->generate->handle($this->october))->toBe(0);
});

it('can be limited to one tenant', function () {
    expect($this->generate->handle($this->october, $this->tenantB))->toBe(1)
        ->and($this->list->handle($this->tenantA))->toBe([]);
});

it('marks fees paid and unpaid, hiding other tenants', function () {
    $this->generate->handle($this->october);
    $fee = $this->list->handle($this->tenantA)[0];

    $this->pay->handle($this->tenantA, $fee->id(), new DateTimeImmutable('2026-10-05'));
    expect($fee->paidOn()?->format('Y-m-d'))->toBe('2026-10-05');

    $this->unpay->handle($this->tenantA, $fee->id());
    expect($fee->paidOn())->toBeNull()
        ->and(fn () => $this->pay->handle($this->tenantB, $fee->id(), new DateTimeImmutable))->toThrow(FeeNotFound::class);
});

it('reports contracts with fees as in use', function () {
    $usage = new FeesContractUsage($this->fees);
    expect($usage->isInUse($this->tenantA, $this->ending))->toBeFalse();

    $this->generate->handle($this->october);

    expect($usage->isInUse($this->tenantA, $this->ending))->toBeTrue()
        ->and($usage->isInUse($this->tenantB, $this->ending))->toBeFalse()
        ->and($usage->isInUse($this->tenantA, new ContractId(99)))->toBeFalse();
});
