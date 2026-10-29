<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\UnitType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreUnitRequest;
use App\Http\Requests\Tenant\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    /**
     * List the units of the current tenant.
     */
    public function index(): Response
    {
        return Inertia::render('tenant/units/index', [
            'units' => Unit::query()
                ->orderBy('code')
                ->get()
                ->map(fn (Unit $unit) => [
                    'id' => $unit->id,
                    'code' => $unit->code,
                    'type' => $unit->type->value,
                    'type_label' => $unit->type->label(),
                    'size_m2' => $unit->size_m2,
                    'monthly_price_cents' => $unit->monthly_price_cents,
                ]),
        ]);
    }

    /**
     * Show the form to create a unit.
     */
    public function create(): Response
    {
        return Inertia::render('tenant/units/create', [
            'types' => UnitType::options(),
        ]);
    }

    /**
     * Store a new unit for the current tenant.
     */
    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::create($request->unitData());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Unidad creada.']);

        return to_route('tenant.units.index');
    }

    /**
     * Show the form to edit a unit.
     */
    public function edit(Unit $unit): Response
    {
        return Inertia::render('tenant/units/edit', [
            'unit' => [
                'id' => $unit->id,
                'code' => $unit->code,
                'type' => $unit->type->value,
                'size_m2' => $unit->size_m2,
                'monthly_price' => number_format($unit->monthly_price_cents / 100, 2, '.', ''),
            ],
            'types' => UnitType::options(),
        ]);
    }

    /**
     * Update a unit of the current tenant.
     */
    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->unitData());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Unidad actualizada.']);

        return to_route('tenant.units.index');
    }
}
