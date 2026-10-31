<?php

namespace GarAge\Billing\Application;

use DateTimeImmutable;
use GarAge\Billing\Domain\Fee;
use GarAge\Billing\Domain\FeeRepository;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: issue a fee for every billing period that has started by the day
 * (a week, month or year of a recurring booking, or a booking of specific
 * days), at the contract's agreed price. Safe to run repeatedly: periods that
 * already have their fee are skipped.
 */
final readonly class GenerateDueFees
{
    public function __construct(
        private ContractRepository $contracts,
        private FeeRepository $fees,
    ) {}

    /**
     * @param  TenantId|null  $tenantId  Only this tenant, or every tenant when null.
     * @return int Number of fees issued.
     */
    public function handle(DateTimeImmutable $day, ?TenantId $tenantId = null): int
    {
        $issued = 0;

        foreach ($this->contracts->startedBy($day, $tenantId) as $contract) {
            $details = $contract->details();

            foreach ($details->schedule->billingPeriodsUntil($day) as $period) {
                if ($this->fees->existsFor($contract->id(), $period->start)) {
                    continue;
                }

                $this->fees->add(Fee::issue($contract->tenantId, $contract->id(), $period, $details->pricePerPeriod));
                $issued++;
            }
        }

        return $issued;
    }
}
