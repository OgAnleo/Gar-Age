<?php

namespace GarAge\Contracts\Application;

use GarAge\Contracts\Domain\RecurringSchedule;
use GarAge\Contracts\Domain\Schedule;
use GarAge\Contracts\Domain\SpecificDaysSchedule;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Pricing\Domain\BookingMode;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Units\Domain\UnitId;

/**
 * Raw data for creating or updating a contract, as it arrives from outside.
 * Recurring modes use a start date and a number of periods (null: indefinite);
 * the "days" mode uses a list of "Y-m-d" dates.
 */
final readonly class ContractInput
{
    /**
     * @param  list<string>  $days
     */
    public function __construct(
        public int $unitId,
        public int $customerId,
        public string $mode,
        public ?string $startsOn = null,
        public ?int $periods = null,
        public array $days = [],
    ) {}

    /**
     * @throws InvalidValue
     */
    public function unitId(): UnitId
    {
        return new UnitId($this->unitId);
    }

    /**
     * @throws InvalidValue
     */
    public function customerId(): CustomerId
    {
        return new CustomerId($this->customerId);
    }

    /**
     * @throws InvalidValue
     */
    public function schedule(): Schedule
    {
        $mode = BookingMode::tryFrom($this->mode) ?? throw new InvalidValue("Unknown booking mode \"{$this->mode}\".");

        if (! $mode->isRecurring()) {
            return SpecificDaysSchedule::fromStrings($this->days);
        }

        $start = $this->startsOn ?? throw new InvalidValue('A recurring booking needs a start date.');

        return new RecurringSchedule($mode, DateRange::day($start), $this->periods);
    }
}
