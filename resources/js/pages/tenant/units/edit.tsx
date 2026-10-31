import UnitController from '@/actions/App/Http/Controllers/Tenant/UnitController';
import FormPage from '@/components/form-page';
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
    return (
        <FormPage title={`Editar ${unit.code}`}>
            <UnitForm
                action={UnitController.update.form({ unit: unit.id })}
                types={types}
                defaults={unit}
                submitLabel="Guardar cambios"
            />
        </FormPage>
    );
}
