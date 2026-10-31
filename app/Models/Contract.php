<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ContractFactory;
use GarAge\Contracts\Domain\Contract as DomainContract;
use GarAge\Contracts\Domain\ContractDetails;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\RecurringSchedule;
use GarAge\Contracts\Domain\Schedule;
use GarAge\Contracts\Domain\SpecificDaysSchedule;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Pricing\Domain\BookingMode;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\Money;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\UnitId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * starts_on/ends_on store the whole span of the schedule (redundant with the
 * mode and period count, or with the days, but handy for date queries).
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $unit_id
 * @property int $customer_id
 * @property BookingMode $mode
 * @property string $starts_on
 * @property string|null $ends_on
 * @property int|null $period_count
 * @property int $price_per_period_cents
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ContractDay> $days
 */
#[Fillable(['unit_id', 'customer_id', 'mode', 'starts_on', 'ends_on', 'period_count', 'price_per_period_cents'])]
class Contract extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<ContractFactory> */
    use HasFactory;

    /**
     * Map this row (with its days) to the Contracts domain entity.
     */
    public function toDomain(): DomainContract
    {
        return DomainContract::restore(
            new ContractId($this->id),
            new TenantId($this->tenant_id),
            new ContractDetails(
                new UnitId($this->unit_id),
                new CustomerId($this->customer_id),
                $this->schedule(),
                Money::fromHundredths($this->price_per_period_cents),
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

    /**
     * @return HasMany<ContractDay, $this>
     */
    public function days(): HasMany
    {
        return $this->hasMany(ContractDay::class)->orderBy('day');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['mode' => BookingMode::class];
    }

    private function schedule(): Schedule
    {
        if ($this->mode->isRecurring()) {
            return new RecurringSchedule($this->mode, DateRange::day($this->starts_on), $this->period_count);
        }

        return SpecificDaysSchedule::fromStrings(array_values($this->days->map(fn (ContractDay $day) => $day->day)->all()));
    }
}
