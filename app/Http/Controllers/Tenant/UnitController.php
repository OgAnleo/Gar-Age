<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreUnitRequest;
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
     * Store a new unit for the current tenant.
     */
    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::create([
            ...$request->safe()->only(['code', 'type', 'size_m2']),
            'monthly_price_cents' => (int) round($request->float('monthly_price') * 100),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Unidad creada.']);

        return to_route('tenant.units.index');
    }
}
