<?php

namespace GarAge\Customers\Domain;

use GarAge\Shared\Domain\InvalidValue;

/**
 * Spanish tax id: DNI (12345678Z), NIE (X1234567L) or company CIF (B12345678).
 * Stored normalized: uppercase, without spaces, dots or dashes.
 */
final readonly class TaxId
{
    public const int MAX_LENGTH = 9;

    private const string CHECK_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';

    public string $value;

    public function __construct(string $value)
    {
        $value = strtoupper((string) preg_replace('/[\s.\-]/', '', $value));

        if (! self::isPersonalId($value) && ! self::isCompanyId($value)) {
            throw new InvalidValue("\"{$value}\" is not a valid DNI, NIE or CIF.");
        }

        $this->value = $value;
    }

    public function equals(self $other): bool
    {
        return $other->value === $this->value;
    }

    /**
     * DNI or NIE: the final letter is a checksum of the number (mod 23).
     */
    private static function isPersonalId(string $value): bool
    {
        if (preg_match('/^(\d{8}|[XYZ]\d{7})([A-Z])$/', $value, $m) !== 1) {
            return false;
        }

        $number = (int) strtr($m[1], 'XYZ', '012');

        return self::CHECK_LETTERS[$number % 23] === $m[2];
    }

    /**
     * CIF of a company: format only, its control digit is not verified yet.
     */
    private static function isCompanyId(string $value): bool
    {
        return preg_match('/^[ABCDEFGHJNPQRSUVW]\d{7}[0-9A-J]$/', $value) === 1;
    }
}
