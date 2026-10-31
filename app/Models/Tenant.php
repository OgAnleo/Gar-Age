<?php

namespace App\Models;

use Database\Factories\TenantFactory;
use GarAge\Tenancy\Domain\Tenant as DomainTenant;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Tenancy\Domain\TenantSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'slug'])]
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    /**
     * Use the slug in generated URLs: route('tenant.dashboard', $tenant).
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Map this row to the Tenancy domain entity.
     */
    public function toDomain(): DomainTenant
    {
        return new DomainTenant(new TenantId($this->id), $this->name, new TenantSlug($this->slug));
    }

    /**
     * Users that belong to this tenant (owners and staff).
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Unit, $this>
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * @return HasMany<Customer, $this>
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
