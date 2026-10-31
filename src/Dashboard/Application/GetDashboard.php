<?php

namespace GarAge\Dashboard\Application;

use DateTimeImmutable;
use GarAge\Billing\Domain\Fee;
use GarAge\Billing\Domain\FeeRepository;
use GarAge\Billing\Domain\FeeStatus;
use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Dashboard\Domain\FeeTally;
use GarAge\Dashboard\Domain\Occupancy;
use GarAge\Shared\Domain\Money;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\Unit;
use GarAge\Units\Domain\UnitRepository;

/**
 * Use case: the dashboard figures of a tenant on a day. Occupancy counts the
 * units taken that day; revenue is per calendar month (collected = fees paid
 * that month, issued = fees whose period starts that month).
 */
final readonly class GetDashboard
{
    public const int REVENUE_MONTHS = 6;

    public function __construct(
        private UnitRepository $units,
        private ContractRepository $contracts,
        private FeeRepository $fees,
    ) {}

    public function handle(TenantId $tenantId, DateTimeImmutable $today): DashboardSummary
    {
        $units = $this->units->allOfTenant($tenantId);
        $contracts = $this->contracts->allOfTenant($tenantId);
        $fees = $this->fees->allOfTenant($tenantId);
        $freeUnits = $this->freeUnits($units, $contracts, $today);
        $overdue = $this->withStatus($fees, FeeStatus::Overdue, $today);

        return new DashboardSummary(
            occupancy: new Occupancy(count($units) - count($freeUnits), count($units)),
            freeUnits: $freeUnits,
            collectedByMonth: $this->collectedByMonth($fees, $today),
            issuedThisMonth: FeeTally::of(array_values(array_filter(
                $fees,
                fn (Fee $fee): bool => $fee->period->start->format('Y-m') === $today->format('Y-m'),
            )))->amount,
            pending: FeeTally::of($this->withStatus($fees, FeeStatus::Pending, $today)),
            overdue: FeeTally::of($overdue),
            overdueFees: $this->withContracts($overdue, $contracts),
        );
    }

    /**
     * @param  list<Unit>  $units
     * @param  list<Contract>  $contracts
     * @return list<Unit>
     */
    private function freeUnits(array $units, array $contracts, DateTimeImmutable $today): array
    {
        $taken = [];
        foreach ($contracts as $contract) {
            if ($contract->details()->schedule->occupies($today)) {
                $taken[$contract->details()->unitId->value] = true;
            }
        }

        return array_values(array_filter($units, fn (Unit $unit): bool => ! isset($taken[$unit->id()->value])));
    }

    /**
     * @param  list<Fee>  $fees
     * @return non-empty-array<string, Money>
     */
    private function collectedByMonth(array $fees, DateTimeImmutable $today): array
    {
        $firstOfMonth = $today->modify('first day of this month');
        $collected = [];
        for ($monthsAgo = self::REVENUE_MONTHS - 1; $monthsAgo >= 0; $monthsAgo--) {
            $collected[$firstOfMonth->modify("-{$monthsAgo} months")->format('Y-m')] = Money::fromHundredths(0);
        }

        foreach ($fees as $fee) {
            $month = $fee->paidOn()?->format('Y-m');
            if ($month !== null && isset($collected[$month])) {
                $collected[$month] = $collected[$month]->plus($fee->amount);
            }
        }

        return $collected;
    }

    /**
     * @param  list<Fee>  $fees
     * @return list<Fee>
     */
    private function withStatus(array $fees, FeeStatus $status, DateTimeImmutable $today): array
    {
        return array_values(array_filter($fees, fn (Fee $fee): bool => $fee->statusOn($today) === $status));
    }

    /**
     * @param  list<Fee>  $overdue
     * @param  list<Contract>  $contracts
     * @return list<OverdueFee>
     */
    private function withContracts(array $overdue, array $contracts): array
    {
        $byId = [];
        foreach ($contracts as $contract) {
            $byId[$contract->id()->value] = $contract;
        }

        usort($overdue, fn (Fee $a, Fee $b): int => $a->dueOn() <=> $b->dueOn());

        return array_map(fn (Fee $fee): OverdueFee => new OverdueFee($fee, $byId[$fee->contractId->value] ?? null), $overdue);
    }
}
