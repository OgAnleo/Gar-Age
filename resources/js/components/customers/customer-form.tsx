import { Form } from '@inertiajs/react';
import TextField from '@/components/text-field';
import { Button } from '@/components/ui/button';
import type { RouteFormDefinition } from '@/wayfinder';

export type CustomerFormValues = {
    name: string;
    tax_id: string;
    email: string | null;
    phone: string | null;
};

type Props = {
    action: RouteFormDefinition<'post'>;
    defaults?: CustomerFormValues;
    submitLabel: string;
};

export default function CustomerForm({ action, defaults, submitLabel }: Props) {
    return (
        <Form {...action} className="space-y-6">
            {({ processing, errors }) => (
                <>
                    <TextField
                        label="Nombre"
                        name="name"
                        defaultValue={defaults?.name}
                        error={errors.name}
                        required
                        maxLength={120}
                        placeholder="Ana López"
                    />
                    <TextField
                        label="DNI, NIE o CIF"
                        name="tax_id"
                        defaultValue={defaults?.tax_id}
                        error={errors.tax_id}
                        required
                        maxLength={20}
                        placeholder="12345678Z"
                    />
                    <TextField
                        label="Email (opcional)"
                        name="email"
                        type="email"
                        defaultValue={defaults?.email ?? ''}
                        error={errors.email}
                    />
                    <TextField
                        label="Teléfono (opcional)"
                        name="phone"
                        type="tel"
                        defaultValue={defaults?.phone ?? ''}
                        error={errors.phone}
                        placeholder="600 111 222"
                    />

                    <Button disabled={processing}>{submitLabel}</Button>
                </>
            )}
        </Form>
    );
}
