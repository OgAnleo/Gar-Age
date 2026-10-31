import { Head } from '@inertiajs/react';
import UnitController from '@/actions/App/Http/Controllers/Tenant/UnitController';
import Heading from '@/components/heading';
import UnitForm, { type UnitTypeOption } from '@/components/units/unit-form';

export default function UnitsCreate({ types }: { types: UnitTypeOption[] }) {
    return (
        <>
            <Head title="Nueva unidad" />
            <div className="max-w-xl p-4">
                <Heading title="Nueva unidad" />
                <UnitForm
                    action={UnitController.store.form()}
                    types={types}
                    submitLabel="Crear unidad"
                />
            </div>
        </>
    );
}
