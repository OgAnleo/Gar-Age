<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\UnitFactory;
use GarAge\Shared\Domain\Money;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\Area;
use GarAge\Units\Domain\Unit as DomainUnit;
use GarAge\Units\Domain\UnitCode;
use GarAge\Units\Domain\UnitDetails;
use GarAge\Units\Domain\UnitId;
use GarAge\Units\Domain\UnitType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $code
 * @property UnitType $type
 * @property string $size_m2
 * @property int $daily_price_cents
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['code', 'type', 'size_m2', 'daily_price_cents'])]
class Unit extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<UnitFactory> */
    use HasFactory;

    /**
     * Map this row to the Units domain entity.
     */
    public function toDomain(): DomainUnit
    {
        return DomainUnit::restore(
            new UnitId($this->id),
            new TenantId($this->tenant_id),
            new UnitDetails(
                new UnitCode($this->code),
                $this->type,
                Area::fromString($this->size_m2),
                Money::fromHundredths($this->daily_price_cents),
            ),
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => UnitType::class,
            'size_m2' => 'decimal:2',
        ];
    }
}
