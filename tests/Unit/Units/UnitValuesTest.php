<?php

use GarAge\Shared\Domain\InvalidValue;
use GarAge\Units\Domain\Area;
use GarAge\Units\Domain\Money;
use GarAge\Units\Domain\UnitCode;
use GarAge\Units\Domain\UnitId;

it('parses money exactly, without floating point', function () {
    expect(Money::fromString('79.99')->cents())->toBe(7999)
        ->and(Money::fromString('19.9')->cents())->toBe(1990)
        ->and(Money::fromString('0')->cents())->toBe(0)
        ->and(Money::fromHundredths(5050)->toDecimalString())->toBe('50.50');
});

it('formats areas with two decimals', function () {
    expect(Area::fromString('12.5')->toDecimalString())->toBe('12.50')
        ->and(Area::fromString('8')->hundredths)->toBe(800);
});

it('rejects malformed money', function (string $value) {
    expect(fn () => Money::fromString($value))->toThrow(InvalidValue::class);
})->with(['exponent' => '1e3', 'negative' => '-1', 'three decimals' => '12.345', 'text' => 'abc']);

it('rejects areas out of range and negative amounts', function () {
    expect(fn () => Area::fromString('0'))->toThrow(InvalidValue::class)
        ->and(fn () => Area::fromString('10000'))->toThrow(InvalidValue::class)
        ->and(fn () => Money::fromHundredths(-1))->toThrow(InvalidValue::class);
});

it('trims unit codes and limits their length', function () {
    expect((new UnitCode('  G-01 '))->value)->toBe('G-01')
        ->and(fn () => new UnitCode('   '))->toThrow(InvalidValue::class)
        ->and(fn () => new UnitCode(str_repeat('x', 21)))->toThrow(InvalidValue::class);
});

it('only accepts positive ids', function () {
    expect((new UnitId(3))->equals(new UnitId(3)))->toBeTrue()
        ->and(fn () => new UnitId(0))->toThrow(InvalidValue::class);
});
