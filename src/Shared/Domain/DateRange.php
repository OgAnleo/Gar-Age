<?php

namespace GarAge\Shared\Domain;

use DateTimeImmutable;

/**
 * Period of whole days, both ends included. A null end means open-ended.
 */
final readonly class DateRange
{
    private function __construct(
        public DateTimeImmutable $start,
        public ?DateTimeImmutable $end,
    ) {}

    /**
     * Build a range from "Y-m-d" dates.
     *
     * @throws InvalidValue
     */
    public static function fromStrings(string $start, ?string $end = null): self
    {
        $range = new self(self::parse($start), $end === null ? null : self::parse($end));

        if ($range->end !== null && $range->end < $range->start) {
            throw new InvalidValue('The end date cannot be before the start date.');
        }

        return $range;
    }

    public function overlaps(self $other): bool
    {
        return ($this->end === null || $other->start <= $this->end)
            && ($other->end === null || $this->start <= $other->end);
    }

    public function contains(DateTimeImmutable $day): bool
    {
        $day = self::parse($day->format('Y-m-d'));

        return $this->start <= $day && ($this->end === null || $day <= $this->end);
    }

    public function startString(): string
    {
        return $this->start->format('Y-m-d');
    }

    public function endString(): ?string
    {
        return $this->end?->format('Y-m-d');
    }

    private static function parse(string $date): DateTimeImmutable
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
            throw new InvalidValue("\"{$date}\" is not a valid Y-m-d date.");
        }

        return $parsed;
    }
}
