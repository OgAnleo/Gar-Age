<?php

namespace App\Providers;

use GarAge\Billing\Domain\FeeRepository;
use GarAge\Billing\Infrastructure\EloquentFeeRepository;
use GarAge\Billing\Infrastructure\FeesContractUsage;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Contracts\Domain\ContractUsage;
use GarAge\Contracts\Infrastructure\ContractsCustomerUsage;
use GarAge\Contracts\Infrastructure\ContractsUnitUsage;
use GarAge\Contracts\Infrastructure\EloquentContractRepository;
use GarAge\Customers\Domain\CustomerRepository;
use GarAge\Customers\Domain\CustomerUsage;
use GarAge\Customers\Infrastructure\EloquentCustomerRepository;
use GarAge\Pricing\Domain\PricingConditionsRepository;
use GarAge\Pricing\Infrastructure\EloquentPricingConditionsRepository;
use GarAge\Tenancy\Domain\Slugger;
use GarAge\Tenancy\Domain\TenantEraser;
use GarAge\Tenancy\Domain\TenantRepository;
use GarAge\Tenancy\Infrastructure\EloquentTenantEraser;
use GarAge\Tenancy\Infrastructure\EloquentTenantRepository;
use GarAge\Tenancy\Infrastructure\StrSlugger;
use GarAge\Units\Domain\UnitRepository;
use GarAge\Units\Domain\UnitUsage;
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
        TenantEraser::class => EloquentTenantEraser::class,
        UnitRepository::class => EloquentUnitRepository::class,
        UnitUsage::class => ContractsUnitUsage::class,
        CustomerRepository::class => EloquentCustomerRepository::class,
        CustomerUsage::class => ContractsCustomerUsage::class,
        ContractRepository::class => EloquentContractRepository::class,
        ContractUsage::class => FeesContractUsage::class,
        FeeRepository::class => EloquentFeeRepository::class,
        PricingConditionsRepository::class => EloquentPricingConditionsRepository::class,
    ];
}
