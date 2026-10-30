<?php

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Application\CheckMembership;
use GarAge\Tenancy\Application\FindHomeTenant;
use GarAge\Tenancy\Application\RegisterBusiness;
use GarAge\Tenancy\Application\ResolveTenant;
use GarAge\Tenancy\Domain\Role;
use GarAge\Tenancy\Domain\Slugger;
use GarAge\Tenancy\Domain\TenantNotFound;
use Tests\Doubles\InMemoryTenantRepository;

beforeEach(function () {
    $this->tenants = new InMemoryTenantRepository;
    $slugger = new class implements Slugger
    {
        public function slugify(string $text): string
        {
            return trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($text)), '-');
        }
    };

    $this->register = new RegisterBusiness($this->tenants, $slugger);
    $this->owner = new UserId(7);
});

it('registers a business owned by the user', function () {
    $tenant = $this->register->handle('Trasteros Norte', $this->owner);

    expect($tenant->slug->value)->toBe('trasteros-norte')
        ->and($this->tenants->roleOf($tenant->id, $this->owner))->toBe(Role::Owner);
});

it('makes slugs unique, never reserved and never empty', function () {
    $this->register->handle('Trasteros Norte', $this->owner);

    expect($this->register->handle('Trasteros Norte', $this->owner)->slug->value)->toBe('trasteros-norte-2')
        ->and($this->register->handle('Settings', $this->owner)->slug->value)->toBe('settings-2')
        ->and($this->register->handle('!!!', $this->owner)->slug->value)->toBe('tenant');
});

it('resolves tenants by slug and treats bad slugs as not found', function () {
    $tenant = $this->register->handle('Garajes Sur', $this->owner);
    $resolve = new ResolveTenant($this->tenants);

    expect($resolve->handle('garajes-sur')->id->equals($tenant->id))->toBeTrue()
        ->and(fn () => $resolve->handle('nope'))->toThrow(TenantNotFound::class)
        ->and(fn () => $resolve->handle('Not A Slug!'))->toThrow(TenantNotFound::class);
});

it('knows members and their home tenant', function () {
    $first = $this->register->handle('Primero', $this->owner);
    $this->register->handle('Segundo', $this->owner);
    $stranger = new UserId(99);

    expect((new CheckMembership($this->tenants))->handle($first->id, $this->owner))->toBeTrue()
        ->and((new CheckMembership($this->tenants))->handle($first->id, $stranger))->toBeFalse()
        ->and((new FindHomeTenant($this->tenants))->handle($this->owner)?->slug->value)->toBe('primero')
        ->and((new FindHomeTenant($this->tenants))->handle($stranger))->toBeNull();
});
