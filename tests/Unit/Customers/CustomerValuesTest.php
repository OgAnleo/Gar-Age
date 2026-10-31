<?php

use GarAge\Customers\Domain\CustomerId;
use GarAge\Customers\Domain\CustomerName;
use GarAge\Customers\Domain\Email;
use GarAge\Customers\Domain\PhoneNumber;
use GarAge\Customers\Domain\TaxId;
use GarAge\Shared\Domain\InvalidValue;

it('normalizes and accepts valid tax ids', function (string $raw, string $normalized) {
    expect((new TaxId($raw))->value)->toBe($normalized);
})->with([
    'dni' => ['12345678z', '12345678Z'],
    'dni with dash' => ['12345678-Z', '12345678Z'],
    'nie' => ['x 1234567 l', 'X1234567L'],
    'cif' => ['B12345678', 'B12345678'],
]);

it('rejects invalid tax ids', function (string $value) {
    expect(fn () => new TaxId($value))->toThrow(InvalidValue::class);
})->with([
    'wrong dni letter' => '12345678A',
    'wrong nie letter' => 'X1234567A',
    'too short' => '1234567Z',
    'bad cif letter' => 'I12345678',
    'empty' => '',
]);

it('compares tax ids by value', function () {
    expect((new TaxId('12345678z'))->equals(new TaxId('12345678Z')))->toBeTrue()
        ->and((new TaxId('12345678Z'))->equals(new TaxId('X1234567L')))->toBeFalse();
});

it('trims customer names and limits their length', function () {
    expect((new CustomerName('  Ana López '))->value)->toBe('Ana López')
        ->and(fn () => new CustomerName('  '))->toThrow(InvalidValue::class)
        ->and(fn () => new CustomerName(str_repeat('x', 121)))->toThrow(InvalidValue::class);
});

it('lowercases emails and rejects malformed ones', function () {
    expect((new Email(' Ana@Example.COM '))->value)->toBe('ana@example.com')
        ->and(fn () => new Email('ana@'))->toThrow(InvalidValue::class);
});

it('strips phone separators and rejects malformed numbers', function () {
    expect((new PhoneNumber('+34 600-111 222'))->value)->toBe('+34600111222')
        ->and(fn () => new PhoneNumber('12345'))->toThrow(InvalidValue::class)
        ->and(fn () => new PhoneNumber('600abc222'))->toThrow(InvalidValue::class);
});

it('only accepts positive customer ids', function () {
    expect(fn () => new CustomerId(0))->toThrow(InvalidValue::class);
});
