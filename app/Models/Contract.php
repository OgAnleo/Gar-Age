<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ContractFactory;
use GarAge\Contracts\Domain\Contract as DomainContract;
use GarAge\Contracts\Domain\ContractDetails;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\Money;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $unit_id
 * @property int $customer_id
 * @property string $starts_on
 * @property string|null $ends_on
 * @property int $monthly_price_cents
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['unit_id', 'customer_id', 'starts_on', 'ends_on', 'monthly_price_cents'])]
class Contract extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<ContractFactory> */
    use HasFactory;

    /**
     * Map this row to the Contracts domain entity.
     */
    public function toDomain(): DomainContract
    {
        return DomainContract::restore(
            new ContractId($this->id),
            new TenantId($this->tenant_id),
            new ContractDetails(
                new UnitId($this->unit_id),
                new CustomerId($this->customer_id),
                DateRange::fromStrings($this->starts_on, $this->ends_on),
                Money::fromHundredths($this->monthly_price_cents),
            ),
        );
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
