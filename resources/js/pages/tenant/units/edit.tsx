import { Head, usePage } from '@inertiajs/react';
import UnitController from '@/actions/App/Http/Controllers/Tenant/UnitController';
import Heading from '@/components/heading';
import UnitForm, {
    type UnitFormValues,
    type UnitTypeOption,
} from '@/components/units/unit-form';

export default function UnitsEdit({
    unit,
    types,
}: {
    unit: UnitFormValues & { id: number };
    types: UnitTypeOption[];
}) {
    const { tenant } = usePage().props;

    return (
        <>
            <Head title={`Editar ${unit.code}`} />
            <div className="max-w-xl p-4">
                <Heading title={`Editar ${unit.code}`} />
                <UnitForm
                    action={UnitController.update.form({
                        tenant: tenant?.slug,
                        unit: unit.id,
                    })}
                    types={types}
                    defaults={unit}
                    submitLabel="Guardar cambios"
                />
            </div>
        </>
    );
}
