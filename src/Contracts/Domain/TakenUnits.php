<?php

namespace GarAge\Contracts\Domain;

use DateTimeImmutable;

/**
 * Which units the contracts take on a given day (specific-day bookings only
 * on their days).
 */
final class TakenUnits
{
    /**
     * @param  list<Contract>  $contracts
     * @return array<int, true> Unit ids as keys.
     */
    public static function on(array $contracts, DateTimeImmutable $day): array
    {
        $taken = [];
        foreach ($contracts as $contract) {
            if ($contract->details()->schedule->occupies($day)) {
                $taken[$contract->details()->unitId->value] = true;
            }
        }

        return $taken;
    }
}
