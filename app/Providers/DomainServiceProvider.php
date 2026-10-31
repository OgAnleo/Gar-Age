<?php

namespace App\Providers;

use GarAge\Customers\Domain\CustomerRepository;
use GarAge\Customers\Infrastructure\EloquentCustomerRepository;
use GarAge\Tenancy\Domain\Slugger;
use GarAge\Tenancy\Domain\TenantRepository;
use GarAge\Tenancy\Infrastructure\EloquentTenantRepository;
use GarAge\Tenancy\Infrastructure\StrSlugger;
use GarAge\Units\Domain\UnitRepository;
use GarAge\Units\Infrastructure\EloquentUnitRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Plugs the infrastructure adapters into the domain ports (hexagonal wiring).
 */
class DomainServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        TenantRepository::class => EloquentTenantRepository::class,
        Slugger::class => StrSlugger::class,
        UnitRepository::class => EloquentUnitRepository::class,
        CustomerRepository::class => EloquentCustomerRepository::class,
    ];
}
