import { Head, usePage } from '@inertiajs/react';
import UnitController from '@/actions/App/Http/Controllers/Tenant/UnitController';
import Heading from '@/components/heading';
import UnitForm, { type UnitTypeOption } from '@/components/units/unit-form';

export default function UnitsCreate({ types }: { types: UnitTypeOption[] }) {
    const { tenant } = usePage().props;

    return (
        <>
            <Head title="Nueva unidad" />
            <div className="max-w-xl p-4">
                <Heading title="Nueva unidad" />
                <UnitForm
                    action={UnitController.store.form(tenant?.slug)}
                    types={types}
                    submitLabel="Crear unidad"
                />
            </div>
        </>
    );
}
