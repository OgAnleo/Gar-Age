<?php

use GarAge\Billing\Domain\Fee;
use GarAge\Billing\Domain\FeeAlreadyPaid;
use GarAge\Billing\Domain\FeeId;
use GarAge\Billing\Domain\FeeStatus;
use GarAge\Billing\Domain\YearMonth;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Shared\Domain\Money;
use GarAge\Tenancy\Domain\TenantId;

function octoberFee(): Fee
{
    return Fee::restore(new FeeId(1), new TenantId(1), new ContractId(1), new YearMonth('2026-10'), Money::fromString('80'), null);
}

it('spans the whole month, leap years included', function (string $month, string $first, string $last) {
    $days = (new YearMonth($month))->days();

    expect($days->startString())->toBe($first)
        ->and($days->endString())->toBe($last);
})->with([
    'october' => ['2026-10', '2026-10-01', '2026-10-31'],
    'leap february' => ['2028-02', '2028-02-01', '2028-02-29'],
]);

it('builds the month of a day and rejects malformed months', function () {
    expect(YearMonth::containing(new DateTimeImmutable('2026-03-15 23:59'))->value)->toBe('2026-03')
        ->and(fn () => new YearMonth('2026-13'))->toThrow(InvalidValue::class)
        ->and(fn () => new YearMonth('26-01'))->toThrow(InvalidValue::class);
});

it('is pending during its month and overdue once the month is over', function (string $day, FeeStatus $status) {
    expect(octoberFee()->statusOn(new DateTimeImmutable($day)))->toBe($status)
        ->and($status->label())->toBeString();
})->with([
    'during the month' => ['2026-10-15', FeeStatus::Pending],
    'last day' => ['2026-10-31 22:00', FeeStatus::Pending],
    'next month' => ['2026-11-01', FeeStatus::Overdue],
]);

it('is paid once marked and can be unmarked', function () {
    $fee = octoberFee();

    $fee->markPaid(new DateTimeImmutable('2026-11-20'));

    expect($fee->statusOn(new DateTimeImmutable('2026-12-01')))->toBe(FeeStatus::Paid)
        ->and(fn () => $fee->markPaid(new DateTimeImmutable('2026-11-21')))->toThrow(FeeAlreadyPaid::class);

    $fee->markUnpaid();

    expect($fee->paidOn())->toBeNull();
});
