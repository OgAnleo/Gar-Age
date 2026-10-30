<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Presenters\UnitPresenter;
use App\Http\Requests\Tenant\UnitRequest;
use App\Tenancy\CurrentTenant;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Application\CreateUnit;
use GarAge\Units\Application\DeleteUnit;
use GarAge\Units\Application\GetUnit;
use GarAge\Units\Application\ListUnits;
use GarAge\Units\Application\UpdateUnit;
use GarAge\Units\Domain\UnitId;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP adapter: translates requests into Units use cases.
 */
class UnitController extends Controller
{
    public function __construct(
        private CurrentTenant $currentTenant,
        private UnitPresenter $presenter,
    ) {}

    /**
     * List the units of the current tenant.
     */
    public function index(ListUnits $listUnits): Response
    {
        return Inertia::render('tenant/units/index', [
            'units' => array_map($this->presenter->listItem(...), $listUnits->handle($this->tenantId())),
        ]);
    }

    /**
     * Show the form to create a unit.
     */
    public function create(): Response
    {
        return Inertia::render('tenant/units/create', [
            'types' => $this->presenter->typeOptions(),
        ]);
    }

    /**
     * Store a new unit for the current tenant.
     */
    public function store(UnitRequest $request, CreateUnit $createUnit): RedirectResponse
    {
        $createUnit->handle($this->tenantId(), $request->unitInput());

        return $this->backToIndex('Unidad creada.');
    }

    /**
     * Show the form to edit a unit.
     */
    public function edit(int $unit, GetUnit $getUnit): Response
    {
        return Inertia::render('tenant/units/edit', [
            'unit' => $this->presenter->formValues($getUnit->handle($this->tenantId(), new UnitId($unit))),
            'types' => $this->presenter->typeOptions(),
        ]);
    }

    /**
     * Update a unit of the current tenant.
     */
    public function update(UnitRequest $request, int $unit, UpdateUnit $updateUnit): RedirectResponse
    {
        $updateUnit->handle($this->tenantId(), new UnitId($unit), $request->unitInput());

        return $this->backToIndex('Unidad actualizada.');
    }

    /**
     * Delete a unit of the current tenant.
     */
    public function destroy(int $unit, DeleteUnit $deleteUnit): RedirectResponse
    {
        $deleteUnit->handle($this->tenantId(), new UnitId($unit));

        return $this->backToIndex('Unidad eliminada.');
    }

    private function tenantId(): TenantId
    {
        return $this->currentTenant->require()->id;
    }

    private function backToIndex(string $message): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route('tenant.units.index');
    }
}
