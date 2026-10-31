<?php

namespace GarAge\Shared\Domain;

/**
 * Trimmed, non-empty text with a maximum length. Subclasses set MAX_LENGTH
 * and DESCRIPTION (used in the error message).
 */
abstract readonly class BoundedText
{
    public const int MAX_LENGTH = 255;

    protected const string DESCRIPTION = 'A text';

    public string $value;

    final public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '' || mb_strlen($value) > static::MAX_LENGTH) {
            throw new InvalidValue(sprintf('%s needs 1 to %d characters.', static::DESCRIPTION, static::MAX_LENGTH));
        }

        $this->value = $value;
    }
}
