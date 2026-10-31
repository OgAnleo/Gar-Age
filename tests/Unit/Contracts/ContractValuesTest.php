<?php

use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractDetails;
use GarAge\Contracts\Domain\ContractStatus;
use GarAge\Contracts\Domain\RecurringSchedule;
use GarAge\Contracts\Domain\Schedule;
use GarAge\Contracts\Domain\SpecificDaysSchedule;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Pricing\Domain\BookingMode;
use GarAge\Pricing\Domain\DiscountPercent;
use GarAge\Pricing\Domain\PricingConditions;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Shared\Domain\Money;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitId;

function recurring(BookingMode $mode, string $start, ?int $periods = null): RecurringSchedule
{
    return new RecurringSchedule($mode, DateRange::day($start), $periods);
}

/**
 * @return list<string> "start..end" of each billing period.
 */
function periodsUntil(Schedule $schedule, string $day): array
{
    return array_map(
        fn (DateRange $period) => "{$period->startString()}..{$period->endString()}",
        $schedule->billingPeriodsUntil(new DateTimeImmutable($day)),
    );
}

it('detects overlapping periods, both ends included', function (array $a, array $b, bool $overlap) {
    $first = DateRange::fromStrings(...$a);
    $second = DateRange::fromStrings(...$b);

    expect($first->overlaps($second))->toBe($overlap)
        ->and($second->overlaps($first))->toBe($overlap);
})->with([
    'disjoint' => [['2026-01-01', '2026-01-31'], ['2026-02-01', '2026-02-28'], false],
    'touching on one day' => [['2026-01-01', '2026-01-31'], ['2026-01-31', '2026-02-28'], true],
    'open-ended after' => [['2026-01-01', null], ['2027-05-01', '2027-05-31'], true],
    'open-ended before' => [['2026-06-01', null], ['2026-01-01', '2026-05-31'], false],
]);

it('rejects malformed dates and an end before the start', function (string $start, ?string $end) {
    expect(fn () => DateRange::fromStrings($start, $end))->toThrow(InvalidValue::class);
})->with([
    'end before start' => ['2026-02-01', '2026-01-31'],
    'impossible day' => ['2026-02-30', null],
    'wrong format' => ['01/02/2026', null],
]);

it('bills weekly, monthly and annual periods from the start date', function (BookingMode $mode, string $start, ?int $periods, string $until, array $expected) {
    expect(periodsUntil(recurring($mode, $start, $periods), $until))->toBe($expected);
})->with([
    'weeks' => [BookingMode::Weekly, '2026-10-01', null, '2026-10-08', ['2026-10-01..2026-10-07', '2026-10-08..2026-10-14']],
    'months keep the day' => [BookingMode::Monthly, '2026-10-15', null, '2026-12-01', ['2026-10-15..2026-11-14', '2026-11-15..2026-12-14']],
    'months clamp short months' => [BookingMode::Monthly, '2027-01-31', null, '2027-03-31', ['2027-01-31..2027-02-27', '2027-02-28..2027-03-30', '2027-03-31..2027-04-29']],
    'years' => [BookingMode::Annual, '2026-03-01', null, '2027-03-01', ['2026-03-01..2027-02-28', '2027-03-01..2028-02-29']],
    'stops after its periods' => [BookingMode::Weekly, '2026-10-01', 1, '2026-12-31', ['2026-10-01..2026-10-07']],
    'not started yet' => [BookingMode::Monthly, '2026-11-01', null, '2026-10-31', []],
]);

it('spans its periods, or has no end when indefinite', function () {
    expect(recurring(BookingMode::Monthly, '2026-10-15', 3)->span()->endString())->toBe('2027-01-14')
        ->and(recurring(BookingMode::Annual, '2026-10-15', 1)->span()->endString())->toBe('2027-10-14')
        ->and(recurring(BookingMode::Weekly, '2026-10-15')->span()->endString())->toBeNull();
});

it('validates recurring schedules', function () {
    expect(fn () => recurring(BookingMode::Days, '2026-10-01'))->toThrow(InvalidValue::class)
        ->and(fn () => recurring(BookingMode::Weekly, '2026-10-01', 0))->toThrow(InvalidValue::class)
        ->and(fn () => recurring(BookingMode::Weekly, '2026-10-01', RecurringSchedule::MAX_PERIODS + 1))->toThrow(InvalidValue::class);
});

it('books specific days, sorted and without repetitions, billed once', function () {
    $schedule = SpecificDaysSchedule::fromStrings(['2026-10-20', '2026-10-05', '2026-10-20']);

    expect($schedule->dayStrings())->toBe(['2026-10-05', '2026-10-20'])
        ->and($schedule->mode())->toBe(BookingMode::Days)
        ->and("{$schedule->span()->startString()}..{$schedule->span()->endString()}")->toBe('2026-10-05..2026-10-20')
        ->and(periodsUntil($schedule, '2026-10-04'))->toBe([])
        ->and(periodsUntil($schedule, '2026-10-05'))->toBe(['2026-10-05..2026-10-20'])
        ->and(fn () => SpecificDaysSchedule::fromStrings([]))->toThrow(InvalidValue::class);
});

it('lets specific days interleave with other bookings without clashing', function () {
    $days = SpecificDaysSchedule::fromStrings(['2026-10-05', '2026-10-20']);

    expect($days->overlaps(SpecificDaysSchedule::fromStrings(['2026-10-06', '2026-10-19'])))->toBeFalse()
        ->and($days->overlaps(SpecificDaysSchedule::fromStrings(['2026-10-20'])))->toBeTrue()
        ->and($days->overlaps(recurring(BookingMode::Weekly, '2026-10-12', 1)))->toBeFalse()
        ->and($days->overlaps(recurring(BookingMode::Weekly, '2026-10-14', 1)))->toBeTrue()
        ->and($days->overlaps(recurring(BookingMode::Monthly, '2026-11-01')))->toBeFalse();
});

it('knows whether it takes the unit on a day', function () {
    $days = SpecificDaysSchedule::fromStrings(['2026-10-05', '2026-10-20']);
    $weekly = recurring(BookingMode::Weekly, '2026-10-01', 1);

    expect($days->occupies(new DateTimeImmutable('2026-10-20 18:00')))->toBeTrue()
        ->and($days->occupies(new DateTimeImmutable('2026-10-10')))->toBeFalse()
        ->and($weekly->occupies(new DateTimeImmutable('2026-10-07')))->toBeTrue()
        ->and($weekly->occupies(new DateTimeImmutable('2026-10-08')))->toBeFalse();
});

it('prices each schedule with the business conditions', function () {
    $conditions = new PricingConditions(new DiscountPercent(10), new DiscountPercent(20), new DiscountPercent(30));
    $rate = Money::fromString('2');

    expect(recurring(BookingMode::Weekly, '2026-10-01')->priceWith($conditions, $rate)->cents())->toBe(1260)
        ->and(recurring(BookingMode::Monthly, '2026-10-01')->priceWith($conditions, $rate)->cents())->toBe(4800)
        ->and(SpecificDaysSchedule::fromStrings(['2026-10-01', '2026-10-03'])->priceWith($conditions, $rate)->cents())->toBe(400);
});

it('keeps the agreed price only for the same mode and number of days', function () {
    $monthly = recurring(BookingMode::Monthly, '2026-10-01');
    $twoDays = SpecificDaysSchedule::fromStrings(['2026-10-01', '2026-10-03']);

    expect($monthly->pricedLike(recurring(BookingMode::Monthly, '2027-01-01', 3)))->toBeTrue()
        ->and($monthly->pricedLike(recurring(BookingMode::Annual, '2026-10-01')))->toBeFalse()
        ->and($monthly->pricedLike($twoDays))->toBeFalse()
        ->and($twoDays->pricedLike(SpecificDaysSchedule::fromStrings(['2026-12-01', '2026-12-24'])))->toBeTrue()
        ->and($twoDays->pricedLike(SpecificDaysSchedule::fromStrings(['2026-12-01'])))->toBeFalse();
});

it('knows its status on a given day', function (string $day, ContractStatus $status) {
    $contract = Contract::create(new TenantId(1), new ContractDetails(
        new UnitId(1),
        new CustomerId(1),
        recurring(BookingMode::Monthly, '2026-03-01', 1),
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
