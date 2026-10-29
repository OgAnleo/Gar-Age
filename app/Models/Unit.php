<?php

namespace App\Models;

use App\Enums\UnitType;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\UnitFactory;
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
 * @property int $monthly_price_cents
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['code', 'type', 'size_m2', 'monthly_price_cents'])]
class Unit extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<UnitFactory> */
    use HasFactory;

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
