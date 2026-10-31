<?php

namespace GarAge\Contracts\Application;

use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\ContractNotFound;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Contracts\Domain\UnitAlreadyRented;
use GarAge\Customers\Domain\CustomerNotFound;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitNotFound;

/**
 * Use case: change a contract. It keeps its agreed price unless what the price
 * depends on changes (the unit, the booking mode, or the number of days).
 */
final readonly class UpdateContract
{
    public function __construct(
        private GetContract $getContract,
        private ContractRepository $contracts,
        private ContractRules $rules,
    ) {}

    /**
     * @throws ContractNotFound
     * @throws InvalidValue
     * @throws UnitNotFound
     * @throws CustomerNotFound
     * @throws UnitAlreadyRented
     */
    public function handle(TenantId $tenantId, ContractId $contractId, ContractInput $input): void
    {
        $contract = $this->getContract->handle($tenantId, $contractId);
        $current = $contract->details();
        $details = $this->rules->detailsFor($tenantId, $input, $contractId);

        if ($details->unitId->equals($current->unitId) && $details->schedule->pricedLike($current->schedule)) {
            $details = $details->withPrice($current->pricePerPeriod);
        }

        $contract->changeDetails($details);
        $this->contracts->save($contract);
    }
}
