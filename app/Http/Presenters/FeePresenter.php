<?php

namespace App\Http\Presenters;

use DateTimeImmutable;
use GarAge\Billing\Domain\Fee;
use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractDetails;

/**
 * Output adapter: turns domain fees into the props the React pages expect,
 * showing the unit, customer and booking mode of each fee's contract.
 */
final class FeePresenter
{
    /**
     * @param  list<Fee>  $fees
     * @param  list<Contract>  $contracts
     * @return list<array{id: int, period_start: string, period_end: string, due_on: string, unit_code: string, customer_name: string, mode_label: string, amount_cents: int, paid_on: string|null, status: string, status_label: string}>
     */
    public function listItems(array $fees, array $contracts, TenantLabels $labels, DateTimeImmutable $today): array
    {
        /** @var array<int, ContractDetails> $contractDetails */
        $contractDetails = [];
        foreach ($contracts as $contract) {
            $contractDetails[$contract->id()->value] = $contract->details();
        }

        return array_map(function (Fee $fee) use ($contractDetails, $labels, $today): array {
            $contract = $contractDetails[$fee->contractId->value] ?? null;
            $status = $fee->statusOn($today);

            return [
                'id' => $fee->id()->value,
                'period_start' => $fee->period->startString(),
                'period_end' => (string) $fee->period->endString(),
                'due_on' => $fee->dueOn()->format('Y-m-d'),
                'unit_code' => $contract === null ? '?' : $labels->unitCode($contract->unitId),
                'customer_name' => $contract === null ? '?' : $labels->customerName($contract->customerId),
                'mode_label' => $contract?->schedule->mode()->label() ?? '?',
                'amount_cents' => $fee->amount->cents(),
                'paid_on' => $fee->paidOn()?->format('Y-m-d'),
                'status' => $status->value,
                'status_label' => $status->label(),
            ];
        }, $fees);
    }
}
