<?php

/*
| Hexagonal rules: dependencies only point inward
| (Infrastructure → Application → Domain).
*/

const FRAMEWORK = ['Illuminate', 'Laravel', 'Inertia', 'App'];
const INFRASTRUCTURE = ['GarAge\Tenancy\Infrastructure', 'GarAge\Units\Infrastructure'];
const APPLICATION = ['GarAge\Tenancy\Application', 'GarAge\Units\Application'];

arch('the domain is pure PHP and knows no outer layer')
    ->expect(['GarAge\Shared\Domain', 'GarAge\Tenancy\Domain', 'GarAge\Units\Domain'])
    ->not->toUse([...FRAMEWORK, ...APPLICATION, ...INFRASTRUCTURE]);

arch('use cases depend only on the domain')
    ->expect(APPLICATION)
    ->not->toUse([...FRAMEWORK, ...INFRASTRUCTURE]);

arch('repositories and services of the domain are ports')
    ->expect([
        'GarAge\Tenancy\Domain\TenantRepository',
        'GarAge\Tenancy\Domain\Slugger',
        'GarAge\Units\Domain\UnitRepository',
    ])
    ->toBeInterfaces();
