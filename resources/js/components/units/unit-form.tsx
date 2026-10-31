import { Form } from '@inertiajs/react';
import SelectField from '@/components/select-field';
import type { SelectOption } from '@/components/select-field';
import TextField from '@/components/text-field';
import { Button } from '@/components/ui/button';
import type { RouteFormDefinition } from '@/wayfinder';

export type UnitTypeOption = SelectOption;

export type UnitFormValues = {
    code: string;
    type: string;
    size_m2: string;
    monthly_price: string;
};

type Props = {
    action: RouteFormDefinition<'post'>;
    types: UnitTypeOption[];
    defaults?: UnitFormValues;
    submitLabel: string;
};

export default function UnitForm({
    action,
    types,
    defaults,
    submitLabel,
}: Props) {
    return (
        <Form {...action} className="space-y-6">
            {({ processing, errors }) => (
                <>
                    <TextField
                        label="Código"
                        name="code"
                        defaultValue={defaults?.code}
                        error={errors.code}
                        required
                        maxLength={20}
                        placeholder="G-01"
                    />
                    <SelectField
                        label="Tipo"
                        name="type"
                        options={types}
                        placeholder="Elige un tipo"
                        defaultValue={defaults?.type}
                        error={errors.type}
                    />
                    <TextField
                        label="Superficie (m²)"
                        name="size_m2"
                        type="number"
                        step="0.01"
                        min="0.5"
                        defaultValue={defaults?.size_m2}
                        error={errors.size_m2}
                        required
                    />
                    <TextField
                        label="Precio mensual (€)"
                        name="monthly_price"
                        type="number"
                        step="0.01"
                        min="0"
                        defaultValue={defaults?.monthly_price}
                        error={errors.monthly_price}
                        required
                    />

                    <Button disabled={processing}>{submitLabel}</Button>
                </>
            )}
        </Form>
    );
}
