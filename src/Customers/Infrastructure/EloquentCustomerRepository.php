<?php

namespace GarAge\Customers\Infrastructure;

use App\Models\Customer as CustomerModel;
use GarAge\Customers\Domain\Customer;
use GarAge\Customers\Domain\CustomerDetails;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Customers\Domain\CustomerRepository;
use GarAge\Customers\Domain\TaxId;
use GarAge\Shared\Infrastructure\QueriesByTenant;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Adapter: CustomerRepository port backed by Eloquent.
 */
final class EloquentCustomerRepository implements CustomerRepository
{
    /** @use QueriesByTenant<CustomerModel> */
    use QueriesByTenant;

    public function add(Customer $customer): CustomerId
    {
        $model = new CustomerModel($this->attributes($customer->details()));
        $model->tenant_id = $customer->tenantId->value;
        $model->save();

        return new CustomerId($model->id);
    }

    public function save(Customer $customer): void
    {
        $this->ofTenant($customer->tenantId)
            ->whereKey($customer->id()->value)
            ->update($this->attributes($customer->details()));
    }

    public function remove(Customer $customer): void
    {
        $this->ofTenant($customer->tenantId)->whereKey($customer->id()->value)->delete();
    }

    public function find(TenantId $tenantId, CustomerId $customerId): ?Customer
    {
        return $this->ofTenant($tenantId)->find($customerId->value)?->toDomain();
    }

    public function allOfTenant(TenantId $tenantId): array
    {
        return array_values($this->ofTenant($tenantId)
            ->orderBy('name')
            ->get()
            ->map(fn (CustomerModel $model): Customer => $model->toDomain())
            ->all());
    }

    public function taxIdTaken(TenantId $tenantId, TaxId $taxId, ?CustomerId $except = null): bool
    {
        return $this->existsExcept($this->ofTenant($tenantId)->where('tax_id', $taxId->value), $except);
    }

    protected function modelClass(): string
    {
        return CustomerModel::class;
    }

    /**
     * @return array{name: string, tax_id: string, email: string|null, phone: string|null}
     */
    private function attributes(CustomerDetails $details): array
    {
        return [
            'name' => $details->name->value,
            'tax_id' => $details->taxId->value,
            'email' => $details->email?->value,
            'phone' => $details->phone?->value,
        ];
    }
}
