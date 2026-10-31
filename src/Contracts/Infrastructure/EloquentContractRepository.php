<?php

namespace GarAge\Contracts\Infrastructure;

use App\Models\Contract as ContractModel;
use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractDetails;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Infrastructure\QueriesByTenant;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitId;
use Illuminate\Database\Eloquent\Builder;

/**
 * Adapter: ContractRepository port backed by Eloquent.
 */
final class EloquentContractRepository implements ContractRepository
{
    /** @use QueriesByTenant<ContractModel> */
    use QueriesByTenant;

    public function add(Contract $contract): ContractId
    {
        $model = new ContractModel($this->attributes($contract->details()));
        $model->tenant_id = $contract->tenantId->value;
        $model->save();

        return new ContractId($model->id);
    }

    public function save(Contract $contract): void
    {
        $this->ofTenant($contract->tenantId)
            ->whereKey($contract->id()->value)
            ->update($this->attributes($contract->details()));
    }

    public function remove(Contract $contract): void
    {
        $this->ofTenant($contract->tenantId)->whereKey($contract->id()->value)->delete();
    }

    public function find(TenantId $tenantId, ContractId $contractId): ?Contract
    {
        return $this->ofTenant($tenantId)->find($contractId->value)?->toDomain();
    }

    public function allOfTenant(TenantId $tenantId): array
    {
        return array_values($this->ofTenant($tenantId)
            ->orderByDesc('starts_on')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ContractModel $model): Contract => $model->toDomain())
            ->all());
    }

    public function unitBusyDuring(TenantId $tenantId, UnitId $unitId, DateRange $period, ?ContractId $except = null): bool
    {
        $query = $this->overlappingQuery($this->ofTenant($tenantId), $period)->where('unit_id', $unitId->value);

        return $this->existsExcept($query, $except);
    }

    public function overlapping(DateRange $period, ?TenantId $tenantId = null): array
    {
        $query = $tenantId === null ? $this->allTenants() : $this->ofTenant($tenantId);

        return array_values($this->overlappingQuery($query, $period)
            ->orderBy('id')
            ->get()
            ->map(fn (ContractModel $model): Contract => $model->toDomain())
            ->all());
    }

    public function existsForUnit(TenantId $tenantId, UnitId $unitId): bool
    {
        return $this->ofTenant($tenantId)->where('unit_id', $unitId->value)->exists();
    }

    public function existsForCustomer(TenantId $tenantId, CustomerId $customerId): bool
    {
        return $this->ofTenant($tenantId)->where('customer_id', $customerId->value)->exists();
    }

    protected function modelClass(): string
    {
        return ContractModel::class;
    }

    /**
     * Two periods overlap when each one starts before the other ends
     * (a null end date never ends).
     *
     * @param  Builder<ContractModel>  $query
     * @return Builder<ContractModel>
     */
    private function overlappingQuery(Builder $query, DateRange $period): Builder
    {
        return $query
            ->when($period->endString(), fn (Builder $query, string $end) => $query->where('starts_on', '<=', $end))
            ->where(fn (Builder $query) => $query
                ->whereNull('ends_on')
                ->orWhere('ends_on', '>=', $period->startString()));
    }

    /**
     * @return array{unit_id: int, customer_id: int, starts_on: string, ends_on: string|null, monthly_price_cents: int}
     */
    private function attributes(ContractDetails $details): array
    {
        return [
            'unit_id' => $details->unitId->value,
            'customer_id' => $details->customerId->value,
            'starts_on' => $details->period->startString(),
            'ends_on' => $details->period->endString(),
            'monthly_price_cents' => $details->monthlyPrice->cents(),
        ];
    }
}
