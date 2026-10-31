<?php

use GarAge\Pricing\Application\GetPricingConditions;
use GarAge\Pricing\Application\UpdatePricingConditions;
use GarAge\Pricing\Domain\BookingMode;
use GarAge\Pricing\Domain\DiscountPercent;
use GarAge\Pricing\Domain\PricingConditions;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Shared\Domain\Money;
use GarAge\Tenancy\Domain\TenantId;
use Tests\Doubles\InMemoryPricingConditionsRepository;

it('prices each recurring period from the daily rate minus its own discount', function (BookingMode $mode, int $cents) {
    $conditions = new PricingConditions(new DiscountPercent(10), new DiscountPercent(20), new DiscountPercent(30));

    expect($conditions->periodPrice(Money::fromString('3.33'), $mode)->cents())->toBe($cents);
})->with([
    'weekly: 7 × 3.33 − 10%' => [BookingMode::Weekly, 2098],    // 2331 × 0.9 = 2097.9
    'monthly: 30 × 3.33 − 20%' => [BookingMode::Monthly, 7992],  // 9990 × 0.8
    'annual: 365 × 3.33 − 30%' => [BookingMode::Annual, 85082],  // 121545 × 0.7 = 85081.5
]);

it('charges specific days at the plain daily rate', function () {
    $conditions = new PricingConditions(new DiscountPercent(50), new DiscountPercent(50), new DiscountPercent(50));

    expect($conditions->daysPrice(Money::fromString('3.33'), 4)->cents())->toBe(1332)
        ->and($conditions->discountFor(BookingMode::Days)->value)->toBe(0)
        ->and(fn () => $conditions->periodPrice(Money::fromString('1'), BookingMode::Days))->toThrow(LogicException::class);
});

it('describes each booking mode', function (BookingMode $mode) {
    expect($mode->label())->toBeString()
        ->and($mode->isRecurring())->toBe($mode->daysPerPeriod() !== null);
})->with(BookingMode::cases());

it('only accepts discounts from 0 to 100%', function () {
    expect(fn () => new DiscountPercent(-1))->toThrow(InvalidValue::class)
        ->and(fn () => new DiscountPercent(101))->toThrow(InvalidValue::class)
        ->and((new DiscountPercent(100))->value)->toBe(100);
});

it('stores the conditions of each business, with no discounts by default', function () {
    $repository = new InMemoryPricingConditionsRepository;
    $tenant = new TenantId(1);

    expect((new GetPricingConditions($repository))->handle($tenant)->monthly->value)->toBe(0);

    (new UpdatePricingConditions($repository))->handle($tenant, weekly: 5, monthly: 10, annual: 20);

    $conditions = (new GetPricingConditions($repository))->handle($tenant);
    expect([$conditions->weekly->value, $conditions->monthly->value, $conditions->annual->value])->toBe([5, 10, 20])
        ->and($repository->forTenant(new TenantId(2))->annual->value)->toBe(0);
});
