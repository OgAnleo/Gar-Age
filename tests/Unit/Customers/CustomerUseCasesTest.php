<?php

use GarAge\Customers\Application\CreateCustomer;
use GarAge\Customers\Application\CustomerInput;
use GarAge\Customers\Application\DeleteCustomer;
use GarAge\Customers\Application\GetCustomer;
use GarAge\Customers\Application\ListCustomers;
use GarAge\Customers\Application\UpdateCustomer;
use GarAge\Customers\Domain\Customer;
use GarAge\Customers\Domain\CustomerNotFound;
use GarAge\Customers\Domain\CustomerTaxIdAlreadyUsed;
use GarAge\Customers\Domain\UniqueCustomerTaxId;
use GarAge\Tenancy\Domain\TenantId;
use Tests\Doubles\InMemoryCustomerRepository;

function customerInput(string $name = 'Ana López', string $taxId = '12345678Z', ?string $email = null): CustomerInput
{
    return new CustomerInput(name: $name, taxId: $taxId, email: $email, phone: '600111222');
}

beforeEach(function () {
    $customers = new InMemoryCustomerRepository;
    $uniqueTaxId = new UniqueCustomerTaxId($customers);
    $getCustomer = new GetCustomer($customers);

    $this->create = new CreateCustomer($customers, $uniqueTaxId);
    $this->list = new ListCustomers($customers);
    $this->get = $getCustomer;
    $this->update = new UpdateCustomer($getCustomer, $customers, $uniqueTaxId);
    $this->delete = new DeleteCustomer($getCustomer, $customers);

    $this->tenantA = new TenantId(1);
    $this->tenantB = new TenantId(2);
});

it('creates customers and lists them ordered by name', function () {
    $this->create->handle($this->tenantA, customerInput('Bea', '12345678Z'));
    $this->create->handle($this->tenantA, customerInput('Ana', 'X1234567L'));
    $this->create->handle($this->tenantB, customerInput('Carlos', '12345678Z'));

    $names = array_map(fn (Customer $customer) => $customer->details()->name->value, $this->list->handle($this->tenantA));

    expect($names)->toBe(['Ana', 'Bea']);
});

it('keeps optional contact data empty when not given', function () {
    $id = $this->create->handle($this->tenantA, new CustomerInput(name: 'Ana', taxId: '12345678Z'));

    $details = $this->get->handle($this->tenantA, $id)->details();

    expect($details->email)->toBeNull()
        ->and($details->phone)->toBeNull();
});

it('rejects a tax id already used in the same tenant, whatever its format', function () {
    $this->create->handle($this->tenantA, customerInput(taxId: '12345678Z'));

    expect(fn () => $this->create->handle($this->tenantA, customerInput(taxId: '12345678-z')))
        ->toThrow(CustomerTaxIdAlreadyUsed::class);
});

it('allows the same tax id in another tenant', function () {
    $this->create->handle($this->tenantA, customerInput());
    $this->create->handle($this->tenantB, customerInput());

    expect($this->list->handle($this->tenantB))->toHaveCount(1);
});

it('updates a customer keeping its own tax id but not taking another one', function () {
    $id = $this->create->handle($this->tenantA, customerInput(taxId: '12345678Z'));
    $this->create->handle($this->tenantA, customerInput(taxId: 'X1234567L'));

    $this->update->handle($this->tenantA, $id, customerInput('Ana María', '12345678Z', 'ana@example.com'));

    $details = $this->get->handle($this->tenantA, $id)->details();

    expect($details->name->value)->toBe('Ana María')
        ->and($details->email?->value)->toBe('ana@example.com')
        ->and(fn () => $this->update->handle($this->tenantA, $id, customerInput(taxId: 'X1234567L')))
        ->toThrow(CustomerTaxIdAlreadyUsed::class);
});

it('hides the customers of other tenants', function () {
    $id = $this->create->handle($this->tenantA, customerInput());

    expect(fn () => $this->get->handle($this->tenantB, $id))->toThrow(CustomerNotFound::class)
        ->and(fn () => $this->update->handle($this->tenantB, $id, customerInput()))->toThrow(CustomerNotFound::class)
        ->and(fn () => $this->delete->handle($this->tenantB, $id))->toThrow(CustomerNotFound::class);
});

it('deletes a customer', function () {
    $id = $this->create->handle($this->tenantA, customerInput());

    $this->delete->handle($this->tenantA, $id);

    expect($this->list->handle($this->tenantA))->toBe([]);
});
