<?php

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Application\AddStaffMember;
use GarAge\Tenancy\Application\CheckMembership;
use GarAge\Tenancy\Application\DeleteBusiness;
use GarAge\Tenancy\Application\FindHomeTenant;
use GarAge\Tenancy\Application\GetMemberRole;
use GarAge\Tenancy\Application\ListMembers;
use GarAge\Tenancy\Application\ListOwnedBusinesses;
use GarAge\Tenancy\Application\RegisterBusiness;
use GarAge\Tenancy\Application\RemoveStaffMember;
use GarAge\Tenancy\Application\RequireOwner;
use GarAge\Tenancy\Application\ResolveTenant;
use GarAge\Tenancy\Domain\AlreadyMember;
use GarAge\Tenancy\Domain\Member;
use GarAge\Tenancy\Domain\NotStaffMember;
use GarAge\Tenancy\Domain\NotTenantOwner;
use GarAge\Tenancy\Domain\Role;
use GarAge\Tenancy\Domain\Slugger;
use GarAge\Tenancy\Domain\Tenant;
use GarAge\Tenancy\Domain\TenantEraser;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Tenancy\Domain\TenantNotFound;
use Tests\Doubles\InMemoryTenantRepository;
use Tests\Doubles\InMemoryUserDirectory;

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

it('lets only an owner delete a business, with all its data', function () {
    $tenants = $this->tenants;
    $eraser = new class($tenants) implements TenantEraser
    {
        public function __construct(private InMemoryTenantRepository $tenants) {}

        public function erase(TenantId $tenantId): void
        {
            $this->tenants->remove($tenantId);
        }
    };
    $delete = new DeleteBusiness(new RequireOwner($tenants), $eraser);
    $tenant = $this->register->handle('Trasteros Norte', $this->owner);
    $staff = new UserId(8);
    $tenants->addMember($tenant->id, $staff, Role::Staff);

    expect(fn () => $delete->handle($tenant->id, $staff))->toThrow(NotTenantOwner::class)
        ->and(fn () => $delete->handle($tenant->id, new UserId(9)))->toThrow(NotTenantOwner::class);

    $delete->handle($tenant->id, $this->owner);

    expect($tenants->slugExists($tenant->slug))->toBeFalse();
});

it('lists the businesses a user owns, not those where they are staff', function () {
    $list = new ListOwnedBusinesses($this->tenants);
    $first = $this->register->handle('Trasteros Norte', $this->owner);
    $this->register->handle('Garajes Sur', $this->owner);
    $other = $this->register->handle('Ajeno', new UserId(8));
    $this->tenants->addMember($other->id, $this->owner, Role::Staff);

    expect(array_map(fn (Tenant $tenant): string => $tenant->name, $list->handle($this->owner)))->toBe(['Trasteros Norte', 'Garajes Sur'])
        ->and($list->handle(new UserId(99)))->toBe([])
        ->and($first->id->value)->toBe(1);
});

it('lets an owner add staff, creating an account with a temporary password when needed', function () {
    $users = new InMemoryUserDirectory;
    $add = new AddStaffMember(new RequireOwner($this->tenants), $this->tenants, $users);
    $tenant = $this->register->handle('Trasteros Norte', $this->owner);
    $existing = $users->add('ana@example.com');

    expect($add->handle($tenant->id, $this->owner, 'Ana', ' ANA@example.com '))->toBeNull()
        ->and($add->handle($tenant->id, $this->owner, 'Luis', 'luis@example.com'))->toBe('temporary-luis@example.com')
        ->and($this->tenants->roleOf($tenant->id, $existing))->toBe(Role::Staff)
        ->and($this->tenants->roleOf($tenant->id, $users->findByEmail('luis@example.com')))->toBe(Role::Staff)
        ->and(fn () => $add->handle($tenant->id, $this->owner, 'Ana', 'ana@example.com'))->toThrow(AlreadyMember::class)
        ->and(fn () => $add->handle($tenant->id, $existing, 'Eva', 'eva@example.com'))->toThrow(NotTenantOwner::class);
});

it('lists members owners first and lets an owner remove staff only', function () {
    $tenant = $this->register->handle('Trasteros Norte', $this->owner);
    $staff = new UserId(3);
    $this->tenants->addMember($tenant->id, $staff, Role::Staff);
    $remove = new RemoveStaffMember(new RequireOwner($this->tenants), $this->tenants);
    $list = new ListMembers($this->tenants);
    $role = new GetMemberRole($this->tenants);

    expect(array_map(fn (Member $member): string => "{$member->userId->value}:{$member->role->value}", $list->handle($tenant->id)))->toBe(['7:owner', '3:staff'])
        ->and($role->handle($tenant->id, $staff))->toBe(Role::Staff)
        ->and(fn () => $remove->handle($tenant->id, $staff, $this->owner))->toThrow(NotTenantOwner::class)
        ->and(fn () => $remove->handle($tenant->id, $this->owner, $this->owner))->toThrow(NotStaffMember::class);

    $remove->handle($tenant->id, $this->owner, $staff);

    expect($role->handle($tenant->id, $staff))->toBeNull();
});
