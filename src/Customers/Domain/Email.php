<?php

namespace GarAge\Customers\Domain;

use GarAge\Shared\Domain\InvalidValue;

final readonly class Email
{
    public const int MAX_LENGTH = 254;

    public string $value;

    public function __construct(string $value)
    {
        $value = mb_strtolower(trim($value));

        if (mb_strlen($value) > self::MAX_LENGTH || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidValue("\"{$value}\" is not a valid email address.");
        }

        $this->value = $value;
    }
}
