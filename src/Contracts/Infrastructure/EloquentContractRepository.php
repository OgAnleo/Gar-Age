<?php

namespace GarAge\Contracts\Infrastructure;

use App\Models\Contract as ContractModel;
use DateTimeImmutable;
use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractDetails;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Contracts\Domain\RecurringSchedule;
use GarAge\Contracts\Domain\SpecificDaysSchedule;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Infrastructure\QueriesByTenant;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Adapter: ContractRepository port backed by Eloquent. The days of a
 * "specific days" booking live in contract_days.
 */
final class EloquentContractRepository implements ContractRepository
{
    /** @use QueriesByTenant<ContractModel> */
    use QueriesByTenant;

    public function add(Contract $contract): ContractId
    {
        return DB::transaction(function () use ($contract): ContractId {
            $model = new ContractModel($this->attributes($contract->details()));
            $model->tenant_id = $contract->tenantId->value;
            $model->save();
            $this->saveDays($model, $contract->details());

            return new ContractId($model->id);
        });
    }

    public function save(Contract $contract): void
    {
        $model = $this->ofTenant($contract->tenantId)->findOrFail($contract->id()->value);

        DB::transaction(function () use ($model, $contract): void {
            $model->update($this->attributes($contract->details()));
            $this->saveDays($model, $contract->details());
        });
    }

    public function remove(Contract $contract): void
    {
        $this->ofTenant($contract->tenantId)->whereKey($contract->id()->value)->delete();
    }

    public function find(TenantId $tenantId, ContractId $contractId): ?Contract
    {
        return $this->ofTenant($tenantId)->with('days')->find($contractId->value)?->toDomain();
    }

    public function allOfTenant(TenantId $tenantId): array
    {
        return $this->toDomain($this->ofTenant($tenantId)->orderByDesc('starts_on')->orderByDesc('id'));
    }

    /**
     * Two spans overlap when each one starts before the other ends (a null end never ends).
     */
    public function ofUnitDuring(TenantId $tenantId, UnitId $unitId, DateRange $period, ?ContractId $except = null): array
    {
        return $this->toDomain($this->ofTenant($tenantId)
            ->where('unit_id', $unitId->value)
            ->when($except, fn (Builder $query, ContractId $except) => $query->whereKeyNot($except->value))
            ->when($period->endString(), fn (Builder $query, string $end) => $query->where('starts_on', '<=', $end))
            ->where(fn (Builder $query) => $query
                ->whereNull('ends_on')
                ->orWhere('ends_on', '>=', $period->startString())));
    }

    public function startedBy(DateTimeImmutable $day, ?TenantId $tenantId = null): array
    {
        $query = $tenantId === null ? $this->allTenants() : $this->ofTenant($tenantId);

        return $this->toDomain($query->where('starts_on', '<=', $day->format('Y-m-d'))->orderBy('id'));
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
     * @param  Builder<ContractModel>  $query
     * @return list<Contract>
     */
    private function toDomain(Builder $query): array
    {
        /** @var Collection<int, ContractModel> $models */
        $models = $query->with('days')->get();

        return array_values($models->map(fn (ContractModel $model): Contract => $model->toDomain())->all());
    }

    private function saveDays(ContractModel $model, ContractDetails $details): void
    {
        $model->days()->delete();

        if ($details->schedule instanceof SpecificDaysSchedule) {
            $model->days()->createMany(array_map(fn (string $day) => ['day' => $day], $details->schedule->dayStrings()));
        }
    }

    /**
     * @return array{unit_id: int, customer_id: int, mode: string, starts_on: string, ends_on: string|null, period_count: int|null, price_per_period_cents: int}
     */
    private function attributes(ContractDetails $details): array
    {
        $schedule = $details->schedule;

        return [
            'unit_id' => $details->unitId->value,
            'customer_id' => $details->customerId->value,
            'mode' => $schedule->mode()->value,
            'starts_on' => $schedule->span()->startString(),
            'ends_on' => $schedule->span()->endString(),
            'period_count' => $schedule instanceof RecurringSchedule ? $schedule->periods : null,
            'price_per_period_cents' => $details->pricePerPeriod->cents(),
        ];
    }
}
