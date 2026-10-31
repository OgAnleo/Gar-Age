<?php

namespace GarAge\Billing\Infrastructure;

use App\Models\Fee as FeeModel;
use DateTimeImmutable;
use GarAge\Billing\Domain\Fee;
use GarAge\Billing\Domain\FeeId;
use GarAge\Billing\Domain\FeeRepository;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Shared\Infrastructure\QueriesByTenant;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Adapter: FeeRepository port backed by Eloquent.
 */
final class EloquentFeeRepository implements FeeRepository
{
    /** @use QueriesByTenant<FeeModel> */
    use QueriesByTenant;

    public function add(Fee $fee): FeeId
    {
        $model = new FeeModel([
            'contract_id' => $fee->contractId->value,
            'period_start' => $fee->period->startString(),
            'period_end' => $fee->period->endString(),
            'amount_cents' => $fee->amount->cents(),
            'paid_on' => $fee->paidOn()?->format('Y-m-d'),
        ]);
        $model->tenant_id = $fee->tenantId->value;
        $model->save();

        return new FeeId($model->id);
    }

    /**
     * Only the payment changes after a fee is issued.
     */
    public function save(Fee $fee): void
    {
        $this->ofTenant($fee->tenantId)
            ->whereKey($fee->id()->value)
            ->update(['paid_on' => $fee->paidOn()?->format('Y-m-d')]);
    }

    public function find(TenantId $tenantId, FeeId $feeId): ?Fee
    {
        return $this->ofTenant($tenantId)->find($feeId->value)?->toDomain();
    }

    public function allOfTenant(TenantId $tenantId): array
    {
        return array_values($this->ofTenant($tenantId)
            ->orderByDesc('period_start')
            ->orderBy('id')
            ->get()
            ->map(fn (FeeModel $model): Fee => $model->toDomain())
            ->all());
    }

    public function existsFor(ContractId $contractId, DateTimeImmutable $periodStart): bool
    {
        return $this->allTenants()
            ->where('contract_id', $contractId->value)
            ->where('period_start', $periodStart->format('Y-m-d'))
            ->exists();
    }

    public function existsForContract(TenantId $tenantId, ContractId $contractId): bool
    {
        return $this->ofTenant($tenantId)->where('contract_id', $contractId->value)->exists();
    }

    protected function modelClass(): string
    {
        return FeeModel::class;
    }
}
