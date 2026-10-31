<?php

namespace GarAge\Customers\Infrastructure;

use App\Models\Customer as CustomerModel;
use App\Tenancy\TenantScope;
use GarAge\Customers\Domain\Customer;
use GarAge\Customers\Domain\CustomerDetails;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Customers\Domain\CustomerRepository;
use GarAge\Customers\Domain\TaxId;
use GarAge\Tenancy\Domain\TenantId;
use Illuminate\Database\Eloquent\Builder;

/**
 * Adapter: CustomerRepository port backed by Eloquent.
 * Scopes every query by tenant explicitly instead of relying on the global scope.
 */
final class EloquentCustomerRepository implements CustomerRepository
{
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
        return $this->ofTenant($tenantId)
            ->where('tax_id', $taxId->value)
            ->when($except !== null, fn (Builder $query) => $query->whereKeyNot($except?->value))
            ->exists();
    }

    /**
     * @return Builder<CustomerModel>
     */
    private function ofTenant(TenantId $tenantId): Builder
    {
        return CustomerModel::withoutGlobalScope(TenantScope::class)->where('tenant_id', $tenantId->value);
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
