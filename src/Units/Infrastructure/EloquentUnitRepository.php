<?php

namespace GarAge\Units\Infrastructure;

use App\Models\Unit as UnitModel;
use App\Tenancy\TenantScope;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\Unit;
use GarAge\Units\Domain\UnitCode;
use GarAge\Units\Domain\UnitDetails;
use GarAge\Units\Domain\UnitId;
use GarAge\Units\Domain\UnitRepository;
use Illuminate\Database\Eloquent\Builder;

/**
 * Adapter: UnitRepository port backed by Eloquent.
 * Scopes every query by tenant explicitly instead of relying on the global scope.
 */
final class EloquentUnitRepository implements UnitRepository
{
    public function add(Unit $unit): UnitId
    {
        $model = new UnitModel($this->attributes($unit->details()));
        $model->tenant_id = $unit->tenantId->value;
        $model->save();

        return new UnitId($model->id);
    }

    public function save(Unit $unit): void
    {
        $this->ofTenant($unit->tenantId)
            ->whereKey($unit->id()->value)
            ->update($this->attributes($unit->details()));
    }

    public function remove(Unit $unit): void
    {
        $this->ofTenant($unit->tenantId)->whereKey($unit->id()->value)->delete();
    }

    public function find(TenantId $tenantId, UnitId $unitId): ?Unit
    {
        return $this->ofTenant($tenantId)->find($unitId->value)?->toDomain();
    }

    public function allOfTenant(TenantId $tenantId): array
    {
        return array_values($this->ofTenant($tenantId)
            ->orderBy('code')
            ->get()
            ->map(fn (UnitModel $model): Unit => $model->toDomain())
            ->all());
    }

    public function codeTaken(TenantId $tenantId, UnitCode $code, ?UnitId $except = null): bool
    {
        return $this->ofTenant($tenantId)
            ->where('code', $code->value)
            ->when($except !== null, fn (Builder $query) => $query->whereKeyNot($except?->value))
            ->exists();
    }

    /**
     * @return Builder<UnitModel>
     */
    private function ofTenant(TenantId $tenantId): Builder
    {
        return UnitModel::withoutGlobalScope(TenantScope::class)->where('tenant_id', $tenantId->value);
    }

    /**
     * @return array{code: string, type: string, size_m2: string, monthly_price_cents: int}
     */
    private function attributes(UnitDetails $details): array
    {
        return [
            'code' => $details->code->value,
            'type' => $details->type->value,
            'size_m2' => $details->area->toDecimalString(),
            'monthly_price_cents' => $details->monthlyPrice->cents(),
        ];
    }
}
