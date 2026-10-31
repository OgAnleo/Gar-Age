<?php

namespace App\Http\Presenters;

use GarAge\Customers\Domain\Customer;

/**
 * Output adapter: turns domain customers into the props the React pages expect.
 */
final class CustomerPresenter
{
    /**
     * Same shape for the list and the edit form.
     *
     * @return array{id: int, name: string, tax_id: string, email: string|null, phone: string|null}
     */
    public function item(Customer $customer): array
    {
        $details = $customer->details();

        return [
            'id' => $customer->id()->value,
            'name' => $details->name->value,
            'tax_id' => $details->taxId->value,
            'email' => $details->email?->value,
            'phone' => $details->phone?->value,
        ];
    }
}
