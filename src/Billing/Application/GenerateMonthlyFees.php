<?php

namespace GarAge\Billing\Application;

use GarAge\Billing\Domain\Fee;
use GarAge\Billing\Domain\FeeRepository;
use GarAge\Billing\Domain\YearMonth;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: issue the fee of a month for every contract running at some point
 * of it, at the agreed monthly price. Safe to run repeatedly: contracts that
 * already have their fee for the month are skipped.
 */
final readonly class GenerateMonthlyFees
{
    public function __construct(
        private ContractRepository $contracts,
        private FeeRepository $fees,
    ) {}

    /**
     * @param  TenantId|null  $tenantId  Only this tenant, or every tenant when null.
     * @return int Number of fees issued.
     */
    public function handle(YearMonth $month, ?TenantId $tenantId = null): int
    {
        $issued = 0;

        foreach ($this->contracts->overlapping($month->days(), $tenantId) as $contract) {
            if ($this->fees->existsFor($contract->id(), $month)) {
                continue;
            }

            $this->fees->add(Fee::issue($contract->tenantId, $contract->id(), $month, $contract->details()->monthlyPrice));
            $issued++;
        }

        return $issued;
    }
}
