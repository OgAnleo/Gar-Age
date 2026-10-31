<?php

namespace GarAge\Units\Infrastructure;

use App\Models\Unit as UnitModel;
use GarAge\Shared\Infrastructure\QueriesByTenant;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\Unit;
use GarAge\Units\Domain\UnitCode;
use GarAge\Units\Domain\UnitDetails;
use GarAge\Units\Domain\UnitId;
use GarAge\Units\Domain\UnitRepository;

/**
 * Adapter: UnitRepository port backed by Eloquent.
 */
final class EloquentUnitRepository implements UnitRepository
{
    /** @use QueriesByTenant<UnitModel> */
    use QueriesByTenant;

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
        return $this->existsExcept($this->ofTenant($tenantId)->where('code', $code->value), $except);
    }

    protected function modelClass(): string
    {
        return UnitModel::class;
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
