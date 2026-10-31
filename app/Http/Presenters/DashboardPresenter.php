<?php

namespace App\Http\Presenters;

use DateTimeImmutable;
use GarAge\Dashboard\Application\DashboardSummary;
use GarAge\Dashboard\Application\OverdueFee;
use GarAge\Dashboard\Domain\FeeTally;
use GarAge\Shared\Domain\Money;
use GarAge\Units\Domain\Unit;

/**
 * Output adapter: turns the dashboard summary into the props of the
 * dashboard page, keeping only the first rows of its lists.
 */
final class DashboardPresenter
{
    public const int LIST_LIMIT = 5;

    public function __construct(private UnitPresenter $units) {}

    /**
     * @return array{
     *     occupancy: array{occupied: int, total: int, percent: int},
     *     revenue: array{collected_cents: int, issued_cents: int, months: list<array{month: string, collected_cents: int}>},
     *     pending: array{count: int, amount_cents: int},
     *     overdue: array{count: int, amount_cents: int},
     *     overdue_fees: list<array{id: int, unit_code: string, customer_name: string, amount_cents: int, due_on: string, days_overdue: int}>,
     *     free_units: list<array{id: int, code: string, type: string, size_m2: string, type_label: string, daily_price_cents: int}>,
     * }
     */
    public function present(DashboardSummary $summary, TenantLabels $labels, DateTimeImmutable $today): array
    {
        $occupancy = $summary->occupancy;

        return [
            'occupancy' => ['occupied' => $occupancy->occupied, 'total' => $occupancy->total, 'percent' => $occupancy->percent()],
            'revenue' => [
                'collected_cents' => $summary->collectedThisMonth()->cents(),
                'issued_cents' => $summary->issuedThisMonth->cents(),
                'months' => array_map(
                    fn (string $month, Money $collected): array => ['month' => $month, 'collected_cents' => $collected->cents()],
                    array_keys($summary->collectedByMonth),
                    array_values($summary->collectedByMonth),
                ),
            ],
            'pending' => $this->tally($summary->pending),
            'overdue' => $this->tally($summary->overdue),
            'overdue_fees' => array_map(
                fn (OverdueFee $overdue): array => $this->overdueFee($overdue, $labels, $today),
                array_slice($summary->overdueFees, 0, self::LIST_LIMIT),
            ),
            'free_units' => array_map(
                fn (Unit $unit): array => $this->units->listItem($unit),
                array_slice($summary->freeUnits, 0, self::LIST_LIMIT),
            ),
        ];
    }

    /**
     * @return array{count: int, amount_cents: int}
     */
    private function tally(FeeTally $tally): array
    {
        return ['count' => $tally->count, 'amount_cents' => $tally->amount->cents()];
    }

    /**
     * @return array{id: int, unit_code: string, customer_name: string, amount_cents: int, due_on: string, days_overdue: int}
     */
    private function overdueFee(OverdueFee $overdue, TenantLabels $labels, DateTimeImmutable $today): array
    {
        $fee = $overdue->fee;
        $contract = $overdue->contract?->details();

        return [
            'id' => $fee->id()->value,
            'unit_code' => $contract === null ? '?' : $labels->unitCode($contract->unitId),
            'customer_name' => $contract === null ? '?' : $labels->customerName($contract->customerId),
            'amount_cents' => $fee->amount->cents(),
            'due_on' => $fee->dueOn()->format('Y-m-d'),
            'days_overdue' => $fee->daysOverdueOn($today),
        ];
    }
}
