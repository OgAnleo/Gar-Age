<?php

namespace App\Http\Presenters;

use DateTimeImmutable;
use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\RecurringSchedule;
use GarAge\Contracts\Domain\SpecificDaysSchedule;
use GarAge\Customers\Domain\Customer;
use GarAge\Pricing\Domain\BookingMode;
use GarAge\Pricing\Domain\PricingConditions;
use GarAge\Units\Domain\Unit;

/**
 * Output adapter: turns domain contracts into the props the React pages expect.
 */
final class ContractPresenter
{
    /**
     * @param  list<Contract>  $contracts
     * @return list<array{id: int, unit_code: string, customer_name: string, mode: string, mode_label: string, starts_on: string, ends_on: string|null, days_count: int|null, price_cents: int, status: string, status_label: string}>
     */
    public function listItems(array $contracts, TenantLabels $labels, DateTimeImmutable $today): array
    {
        return array_map(function (Contract $contract) use ($labels, $today): array {
            $details = $contract->details();
            $schedule = $details->schedule;
            $status = $contract->statusOn($today);

            return [
                'id' => $contract->id()->value,
                'unit_code' => $labels->unitCode($details->unitId),
                'customer_name' => $labels->customerName($details->customerId),
                'mode' => $schedule->mode()->value,
                'mode_label' => $schedule->mode()->label(),
                'starts_on' => $schedule->span()->startString(),
                'ends_on' => $schedule->span()->endString(),
                'days_count' => $schedule instanceof SpecificDaysSchedule ? count($schedule->days) : null,
                'price_cents' => $details->pricePerPeriod->cents(),
                'status' => $status->value,
                'status_label' => $status->label(),
            ];
        }, $contracts);
    }

    /**
     * Values for the edit form.
     *
     * @return array{id: int, unit_id: string, customer_id: string, mode: string, starts_on: string|null, periods: int|null, days: list<string>, price_cents: int}
     */
    public function formValues(Contract $contract): array
    {
        $details = $contract->details();
        $schedule = $details->schedule;

        return [
            'id' => $contract->id()->value,
            'unit_id' => (string) $details->unitId->value,
            'customer_id' => (string) $details->customerId->value,
            'mode' => $schedule->mode()->value,
            'starts_on' => $schedule instanceof RecurringSchedule ? $schedule->span()->startString() : null,
            'periods' => $schedule instanceof RecurringSchedule ? $schedule->periods : null,
            'days' => $schedule instanceof SpecificDaysSchedule ? $schedule->dayStrings() : [],
            'price_cents' => $details->pricePerPeriod->cents(),
        ];
    }

    /**
     * Select options for the form. Each unit carries what each mode would cost
     * today, so the page can show the price without repeating the rules.
     *
     * @param  list<Unit>  $units
     * @param  list<Customer>  $customers
     * @return array{units: list<array{id: int, label: string, prices: array<string, int>}>, customers: list<array{id: int, label: string}>, modes: list<array{value: string, label: string}>}
     */
    public function formOptions(array $units, array $customers, PricingConditions $conditions): array
    {
        $recurring = array_filter(BookingMode::cases(), fn (BookingMode $mode) => $mode->isRecurring());

        return [
            'units' => array_map(function (Unit $unit) use ($recurring, $conditions): array {
                $dailyRate = $unit->details()->dailyPrice;
                $prices = [BookingMode::Days->value => $dailyRate->cents()];
                foreach ($recurring as $mode) {
                    $prices[$mode->value] = $conditions->periodPrice($dailyRate, $mode)->cents();
                }

                return ['id' => $unit->id()->value, 'label' => $unit->details()->code->value, 'prices' => $prices];
            }, $units),
            'customers' => array_map(fn (Customer $customer): array => [
                'id' => $customer->id()->value,
                'label' => $customer->details()->name->value,
            ], $customers),
            'modes' => array_map(fn (BookingMode $mode): array => ['value' => $mode->value, 'label' => $mode->label()], BookingMode::cases()),
        ];
    }
}
