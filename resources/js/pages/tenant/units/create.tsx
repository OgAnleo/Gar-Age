import UnitController from '@/actions/App/Http/Controllers/Tenant/UnitController';
import FormPage from '@/components/form-page';
import UnitForm, { type UnitTypeOption } from '@/components/units/unit-form';

export default function UnitsCreate({ types }: { types: UnitTypeOption[] }) {
    return (
        <FormPage title="Nueva unidad">
            <UnitForm
                action={UnitController.store.form()}
                types={types}
                submitLabel="Crear unidad"
            />
        </FormPage>
    );
}
