import { Form } from '@inertiajs/react';
import { useState } from 'react';
import SelectField from '@/components/select-field';
import TextField from '@/components/text-field';
import { Button } from '@/components/ui/button';
import type { RouteFormDefinition } from '@/wayfinder';

export type UnitOption = { id: number; label: string; monthly_price: string };
export type CustomerOption = { id: number; label: string };

export type ContractFormValues = {
    unit_id: string;
    customer_id: string;
    starts_on: string;
    ends_on: string | null;
    monthly_price: string;
};

export type ContractFormOptions = {
    units: UnitOption[];
    customers: CustomerOption[];
};

type Props = ContractFormOptions & {
    action: RouteFormDefinition<'post'>;
    defaults?: ContractFormValues;
    submitLabel: string;
};

const toOptions = (items: { id: number; label: string }[]) =>
    items.map((item) => ({ value: String(item.id), label: item.label }));

export default function ContractForm({
    action,
    units,
    customers,
    defaults,
    submitLabel,
}: Props) {
    const [price, setPrice] = useState(defaults?.monthly_price ?? '');

    // Choosing a unit proposes its list price; the user can still change it.
    const proposeUnitPrice = (unitId: string) => {
        const unit = units.find((option) => String(option.id) === unitId);

        if (unit) {
            setPrice(unit.monthly_price);
        }
    };

    return (
        <Form {...action} className="space-y-6">
            {({ processing, errors }) => (
                <>
                    <SelectField
                        label="Unidad"
                        name="unit_id"
                        options={toOptions(units)}
                        placeholder="Elige una unidad"
                        defaultValue={defaults?.unit_id}
                        error={errors.unit_id}
                        onValueChange={proposeUnitPrice}
                    />
                    <SelectField
                        label="Cliente"
                        name="customer_id"
                        options={toOptions(customers)}
                        placeholder="Elige un cliente"
                        defaultValue={defaults?.customer_id}
                        error={errors.customer_id}
                    />
                    <div className="grid gap-6 sm:grid-cols-2">
                        <TextField
                            label="Inicio"
                            name="starts_on"
                            type="date"
                            defaultValue={defaults?.starts_on}
                            error={errors.starts_on}
                            required
                        />
                        <TextField
                            label="Fin (opcional)"
                            name="ends_on"
                            type="date"
                            defaultValue={defaults?.ends_on ?? ''}
                            error={errors.ends_on}
                        />
                    </div>
                    <TextField
                        label="Precio mensual pactado (€)"
                        name="monthly_price"
                        type="number"
                        step="0.01"
                        min="0"
                        value={price}
                        onChange={(event) => setPrice(event.target.value)}
                        error={errors.monthly_price}
                        required
                    />

                    <Button disabled={processing}>{submitLabel}</Button>
                </>
            )}
        </Form>
    );
}
