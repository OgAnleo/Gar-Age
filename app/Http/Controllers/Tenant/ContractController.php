<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Presenters\ContractPresenter;
use App\Http\Presenters\TenantLabels;
use App\Http\Requests\Tenant\ContractRequest;
use App\Tenancy\CurrentTenant;
use GarAge\Contracts\Application\CreateContract;
use GarAge\Contracts\Application\DeleteContract;
use GarAge\Contracts\Application\GetContract;
use GarAge\Contracts\Application\ListContracts;
use GarAge\Contracts\Application\UpdateContract;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Customers\Application\ListCustomers;
use GarAge\Pricing\Application\GetPricingConditions;
use GarAge\Units\Application\ListUnits;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP adapter: translates requests into Contracts use cases.
 */
class ContractController extends TenantController
{
    public function __construct(
        CurrentTenant $currentTenant,
        private ContractPresenter $presenter,
        private ListUnits $listUnits,
        private ListCustomers $listCustomers,
        private GetPricingConditions $getPricingConditions,
    ) {
        parent::__construct($currentTenant);
    }

    /**
     * List the contracts of the current tenant with their status today.
     */
    public function index(ListContracts $listContracts): Response
    {
        $tenantId = $this->tenantId();

        $labels = TenantLabels::from($this->listUnits->handle($tenantId), $this->listCustomers->handle($tenantId));

        return Inertia::render('tenant/contracts/index', [
            'contracts' => $this->presenter->listItems($listContracts->handle($tenantId), $labels, $this->today()),
        ]);
    }

    /**
     * Show the form to create a contract.
     */
    public function create(): Response
    {
        return Inertia::render('tenant/contracts/create', $this->formOptions());
    }

    /**
     * Store a new contract for the current tenant.
     */
    public function store(ContractRequest $request, CreateContract $createContract): RedirectResponse
    {
        $createContract->handle($this->tenantId(), $request->contractInput());

        return $this->backToIndex('Contrato creado.');
    }

    /**
     * Show the form to edit a contract.
     */
    public function edit(int $contract, GetContract $getContract): Response
    {
        return Inertia::render('tenant/contracts/edit', [
            'contract' => $this->presenter->formValues($getContract->handle($this->tenantId(), new ContractId($contract))),
            ...$this->formOptions(),
        ]);
    }

    /**
     * Update a contract of the current tenant.
     */
    public function update(ContractRequest $request, int $contract, UpdateContract $updateContract): RedirectResponse
    {
        $updateContract->handle($this->tenantId(), new ContractId($contract), $request->contractInput());

        return $this->backToIndex('Contrato actualizado.');
    }

    /**
     * Delete a contract of the current tenant.
     */
    public function destroy(int $contract, DeleteContract $deleteContract): RedirectResponse
    {
        $deleteContract->handle($this->tenantId(), new ContractId($contract));

        return $this->backToIndex('Contrato eliminado.');
    }

    /**
     * @return array{units: list<array{id: int, label: string, prices: array<string, int>}>, customers: list<array{id: int, label: string}>, modes: list<array{value: string, label: string}>}
     */
    private function formOptions(): array
    {
        $tenantId = $this->tenantId();

        return $this->presenter->formOptions(
            $this->listUnits->handle($tenantId),
            $this->listCustomers->handle($tenantId),
            $this->getPricingConditions->handle($tenantId),
        );
    }

    private function backToIndex(string $message): RedirectResponse
    {
        return $this->redirectWithToast('tenant.contracts.index', $message);
    }
}
