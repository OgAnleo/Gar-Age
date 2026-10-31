<?php

namespace GarAge\Customers\Domain;

use GarAge\Shared\Domain\InvalidValue;

/**
 * Phone kept as digits with an optional leading "+" (e.g. +34600111222).
 */
final readonly class PhoneNumber
{
    public string $value;

    public function __construct(string $value)
    {
        $value = (string) preg_replace('/[\s.\-()]/', '', $value);

        if (preg_match('/^\+?\d{9,15}$/', $value) !== 1) {
            throw new InvalidValue("\"{$value}\" is not a valid phone number.");
        }

        $this->value = $value;
    }
}
