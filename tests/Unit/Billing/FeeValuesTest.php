<?php

use GarAge\Billing\Domain\Fee;
use GarAge\Billing\Domain\FeeAlreadyPaid;
use GarAge\Billing\Domain\FeeId;
use GarAge\Billing\Domain\FeeStatus;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\Money;
use GarAge\Tenancy\Domain\TenantId;

function octoberFee(): Fee
{
    return Fee::restore(
        new FeeId(1),
        new TenantId(1),
        new ContractId(1),
        DateRange::fromStrings('2026-10-01', '2026-10-31'),
        Money::fromString('80'),
        null,
    );
}

it('is due when its period starts and overdue after the grace days', function (string $day, FeeStatus $status) {
    expect(octoberFee()->statusOn(new DateTimeImmutable($day)))->toBe($status)
        ->and($status->label())->toBeString();
})->with([
    'before its period' => ['2026-09-20', FeeStatus::Pending],
    'last day to pay' => ['2026-10-08 22:00', FeeStatus::Pending],
    'after the grace days' => ['2026-10-09', FeeStatus::Overdue],
]);

it('knows its due date', function () {
    expect(octoberFee()->dueOn()->format('Y-m-d'))->toBe('2026-10-08');
});

it('counts the days it is overdue, none once paid', function () {
    $fee = octoberFee();

    expect($fee->daysOverdueOn(new DateTimeImmutable('2026-10-08 23:00')))->toBe(0)
        ->and($fee->daysOverdueOn(new DateTimeImmutable('2026-10-31 01:00')))->toBe(23);

    $fee->markPaid(new DateTimeImmutable('2026-10-31'));

    expect($fee->daysOverdueOn(new DateTimeImmutable('2026-11-30')))->toBe(0);
});

it('is paid once marked and can be unmarked', function () {
    $fee = octoberFee();

    $fee->markPaid(new DateTimeImmutable('2026-11-20'));

    expect($fee->statusOn(new DateTimeImmutable('2026-12-01')))->toBe(FeeStatus::Paid)
        ->and(fn () => $fee->markPaid(new DateTimeImmutable('2026-11-21')))->toThrow(FeeAlreadyPaid::class);

    $fee->markUnpaid();

    expect($fee->paidOn())->toBeNull();
});
