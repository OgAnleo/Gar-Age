<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\FeeFactory;
use DateTimeImmutable;
use GarAge\Billing\Domain\Fee as DomainFee;
use GarAge\Billing\Domain\FeeId;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\Money;
use GarAge\Tenancy\Domain\TenantId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $contract_id
 * @property string $period_start
 * @property string $period_end
 * @property int $amount_cents
 * @property string|null $paid_on
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['contract_id', 'period_start', 'period_end', 'amount_cents', 'paid_on'])]
class Fee extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<FeeFactory> */
    use HasFactory;

    /**
     * Map this row to the Billing domain entity.
     */
    public function toDomain(): DomainFee
    {
        return DomainFee::restore(
            new FeeId($this->id),
            new TenantId($this->tenant_id),
            new ContractId($this->contract_id),
            DateRange::fromStrings($this->period_start, $this->period_end),
            Money::fromHundredths($this->amount_cents),
            $this->paid_on === null ? null : new DateTimeImmutable($this->paid_on),
        );
    }
}
