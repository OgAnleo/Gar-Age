<?php

namespace App\Http\Presenters;

use DateTimeImmutable;
use GarAge\Contracts\Domain\Contract;
use GarAge\Customers\Domain\Customer;
use GarAge\Units\Domain\Unit;

/**
 * Output adapter: turns domain contracts into the props the React pages expect.
 */
final class ContractPresenter
{
    /**
     * @param  list<Contract>  $contracts
     * @return list<array{id: int, unit_code: string, customer_name: string, starts_on: string, ends_on: string|null, monthly_price_cents: int, status: string, status_label: string}>
     */
    public function listItems(array $contracts, TenantLabels $labels, DateTimeImmutable $today): array
    {
        return array_map(function (Contract $contract) use ($labels, $today): array {
            $details = $contract->details();
            $status = $contract->statusOn($today);

            return [
                'id' => $contract->id()->value,
                'unit_code' => $labels->unitCode($details->unitId),
                'customer_name' => $labels->customerName($details->customerId),
                'starts_on' => $details->period->startString(),
                'ends_on' => $details->period->endString(),
                'monthly_price_cents' => $details->monthlyPrice->cents(),
                'status' => $status->value,
                'status_label' => $status->label(),
            ];
        }, $contracts);
    }

    /**
     * Values for the edit form (price in euros, as typed by the user).
     *
     * @return array{id: int, unit_id: string, customer_id: string, starts_on: string, ends_on: string|null, monthly_price: string}
     */
    public function formValues(Contract $contract): array
    {
        $details = $contract->details();

        return [
            'id' => $contract->id()->value,
            'unit_id' => (string) $details->unitId->value,
            'customer_id' => (string) $details->customerId->value,
            'starts_on' => $details->period->startString(),
            'ends_on' => $details->period->endString(),
            'monthly_price' => $details->monthlyPrice->toDecimalString(),
        ];
    }

    /**
     * Select options for the form (each unit carries its list price).
     *
     * @param  list<Unit>  $units
     * @param  list<Customer>  $customers
     * @return array{units: list<array{id: int, label: string, monthly_price: string}>, customers: list<array{id: int, label: string}>}
     */
    public function formOptions(array $units, array $customers): array
    {
        return [
            'units' => array_map(fn (Unit $unit): array => [
                'id' => $unit->id()->value,
                'label' => $unit->details()->code->value,
                'monthly_price' => $unit->details()->monthlyPrice->toDecimalString(),
            ], $units),
            'customers' => array_map(fn (Customer $customer): array => [
                'id' => $customer->id()->value,
                'label' => $customer->details()->name->value,
            ], $customers),
        ];
    }
}
