<?php

namespace GarAge\Pricing\Domain;

/**
 * How a customer books a unit: by recurring periods, or by specific days.
 */
enum BookingMode: string
{
    case Annual = 'annual';
    case Monthly = 'monthly';
    case Weekly = 'weekly';
    case Days = 'days';

    public function label(): string
    {
        return match ($this) {
            self::Annual => 'Anual',
            self::Monthly => 'Mensual',
            self::Weekly => 'Semanal',
            self::Days => 'Días sueltos',
        };
    }

    /**
     * Days of daily rate a period is worth before its discount (null: priced per day).
     */
    public function daysPerPeriod(): ?int
    {
        return match ($this) {
            self::Annual => 365,
            self::Monthly => 30,
            self::Weekly => 7,
            self::Days => null,
        };
    }

    public function isRecurring(): bool
    {
        return $this !== self::Days;
    }
}
