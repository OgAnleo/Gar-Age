<?php

namespace GarAge\Dashboard\Domain;

use GarAge\Shared\Domain\InvalidValue;

/**
 * How many of a business's units are taken on a day.
 */
final readonly class Occupancy
{
    /**
     * @throws InvalidValue
     */
    public function __construct(
        public int $occupied,
        public int $total,
    ) {
        if ($occupied < 0 || $occupied > $total) {
            throw new InvalidValue('Occupied units must be between 0 and the total.');
        }
    }

    /**
     * Whole percentage, rounded half up (0 when there are no units).
     */
    public function percent(): int
    {
        return $this->total === 0 ? 0 : intdiv($this->occupied * 200 + $this->total, $this->total * 2);
    }
}
