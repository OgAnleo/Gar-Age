<?php

/*
| Hexagonal rules: dependencies only point inward
| (Infrastructure → Application → Domain).
*/

const FRAMEWORK = ['Illuminate', 'Laravel', 'Inertia', 'App'];
const INFRASTRUCTURE = [
    'GarAge\Tenancy\Infrastructure',
    'GarAge\Units\Infrastructure',
    'GarAge\Customers\Infrastructure',
    'GarAge\Contracts\Infrastructure',
    'GarAge\Billing\Infrastructure',
    'GarAge\Pricing\Infrastructure',
];
const APPLICATION = [
    'GarAge\Tenancy\Application',
    'GarAge\Units\Application',
    'GarAge\Customers\Application',
    'GarAge\Contracts\Application',
    'GarAge\Billing\Application',
    'GarAge\Pricing\Application',
];

arch('the domain is pure PHP and knows no outer layer')
    ->expect([
        'GarAge\Shared\Domain',
        'GarAge\Tenancy\Domain',
        'GarAge\Units\Domain',
        'GarAge\Customers\Domain',
        'GarAge\Contracts\Domain',
        'GarAge\Billing\Domain',
        'GarAge\Pricing\Domain',
    ])
    ->not->toUse([...FRAMEWORK, ...APPLICATION, ...INFRASTRUCTURE]);

arch('use cases depend only on the domain')
    ->expect(APPLICATION)
    ->not->toUse([...FRAMEWORK, ...INFRASTRUCTURE]);

arch('repositories and services of the domain are ports')
    ->expect([
        'GarAge\Tenancy\Domain\TenantRepository',
        'GarAge\Tenancy\Domain\Slugger',
        'GarAge\Units\Domain\UnitRepository',
        'GarAge\Units\Domain\UnitUsage',
        'GarAge\Customers\Domain\CustomerRepository',
        'GarAge\Customers\Domain\CustomerUsage',
        'GarAge\Contracts\Domain\ContractRepository',
        'GarAge\Contracts\Domain\ContractUsage',
        'GarAge\Billing\Domain\FeeRepository',
        'GarAge\Pricing\Domain\PricingConditionsRepository',
    ])
    ->toBeInterfaces();
