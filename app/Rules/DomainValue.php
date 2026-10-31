<?php

namespace App\Rules;

use Closure;
use GarAge\Shared\Domain\InvalidValue;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Validation rule that asks a domain value object whether it accepts the input,
 * so a format rule (e.g. the DNI check letter) lives only in the domain.
 */
final readonly class DomainValue implements ValidationRule
{
    /**
     * @param  Closure(string): object  $make  Builds the value object, throwing InvalidValue if rejected.
     */
    public function __construct(
        private Closure $make,
        private string $message,
    ) {}

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            ($this->make)(is_string($value) ? $value : '');
        } catch (InvalidValue) {
            $fail($this->message);
        }
    }
}
