<?php

use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractDetails;
use GarAge\Contracts\Domain\ContractStatus;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Shared\Domain\Money;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitId;

it('detects overlapping periods, both ends included', function (array $a, array $b, bool $overlap) {
    $first = DateRange::fromStrings(...$a);
    $second = DateRange::fromStrings(...$b);

    expect($first->overlaps($second))->toBe($overlap)
        ->and($second->overlaps($first))->toBe($overlap);
})->with([
    'disjoint' => [['2026-01-01', '2026-01-31'], ['2026-02-01', '2026-02-28'], false],
    'touching on one day' => [['2026-01-01', '2026-01-31'], ['2026-01-31', '2026-02-28'], true],
    'contained' => [['2026-01-01', '2026-12-31'], ['2026-03-01', '2026-03-31'], true],
    'open-ended after' => [['2026-01-01', null], ['2027-05-01', '2027-05-31'], true],
    'open-ended before' => [['2026-06-01', null], ['2026-01-01', '2026-05-31'], false],
    'both open-ended' => [['2026-01-01', null], ['2030-01-01', null], true],
]);

it('rejects malformed dates and an end before the start', function (string $start, ?string $end) {
    expect(fn () => DateRange::fromStrings($start, $end))->toThrow(InvalidValue::class);
})->with([
    'end before start' => ['2026-02-01', '2026-01-31'],
    'impossible day' => ['2026-02-30', null],
    'wrong format' => ['01/02/2026', null],
]);

it('formats its ends as Y-m-d', function () {
    $range = DateRange::fromStrings('2026-01-01');

    expect($range->startString())->toBe('2026-01-01')
        ->and($range->endString())->toBeNull();
});

it('knows its status on a given day', function (string $day, ContractStatus $status) {
    $contract = Contract::create(new TenantId(1), new ContractDetails(
        new UnitId(1),
        new CustomerId(1),
        DateRange::fromStrings('2026-03-01', '2026-03-31'),
        Money::fromString('80'),
    ));

    expect($contract->statusOn(new DateTimeImmutable($day)))->toBe($status)
        ->and($status->label())->toBeString();
})->with([
    'before' => ['2026-02-28 23:00', ContractStatus::Upcoming],
    'first day' => ['2026-03-01', ContractStatus::Active],
    'last day' => ['2026-03-31 18:00', ContractStatus::Active],
    'after' => ['2026-04-01', ContractStatus::Finished],
]);
