<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Presenters\CustomerPresenter;
use App\Http\Requests\Tenant\CustomerRequest;
use App\Tenancy\CurrentTenant;
use GarAge\Customers\Application\CreateCustomer;
use GarAge\Customers\Application\DeleteCustomer;
use GarAge\Customers\Application\GetCustomer;
use GarAge\Customers\Application\ListCustomers;
use GarAge\Customers\Application\UpdateCustomer;
use GarAge\Customers\Domain\CustomerId;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP adapter: translates requests into Customers use cases.
 */
class CustomerController extends TenantController
{
    public function __construct(
        CurrentTenant $currentTenant,
        private CustomerPresenter $presenter,
    ) {
        parent::__construct($currentTenant);
    }

    /**
     * List the customers of the current tenant.
     */
    public function index(ListCustomers $listCustomers): Response
    {
        return Inertia::render('tenant/customers/index', [
            'customers' => array_map($this->presenter->item(...), $listCustomers->handle($this->tenantId())),
        ]);
    }

    /**
     * Show the form to create a customer.
     */
    public function create(): Response
    {
        return Inertia::render('tenant/customers/create');
    }

    /**
     * Store a new customer for the current tenant.
     */
    public function store(CustomerRequest $request, CreateCustomer $createCustomer): RedirectResponse
    {
        $createCustomer->handle($this->tenantId(), $request->customerInput());

        return $this->backToIndex('Cliente creado.');
    }

    /**
     * Show the form to edit a customer.
     */
    public function edit(int $customer, GetCustomer $getCustomer): Response
    {
        return Inertia::render('tenant/customers/edit', [
            'customer' => $this->presenter->item($getCustomer->handle($this->tenantId(), new CustomerId($customer))),
        ]);
    }

    /**
     * Update a customer of the current tenant.
     */
    public function update(CustomerRequest $request, int $customer, UpdateCustomer $updateCustomer): RedirectResponse
    {
        $updateCustomer->handle($this->tenantId(), new CustomerId($customer), $request->customerInput());

        return $this->backToIndex('Cliente actualizado.');
    }

    /**
     * Delete a customer of the current tenant.
     */
    public function destroy(int $customer, DeleteCustomer $deleteCustomer): RedirectResponse
    {
        $deleteCustomer->handle($this->tenantId(), new CustomerId($customer));

        return $this->backToIndex('Cliente eliminado.');
    }

    private function backToIndex(string $message): RedirectResponse
    {
        return $this->redirectWithToast('tenant.customers.index', $message);
    }
}
