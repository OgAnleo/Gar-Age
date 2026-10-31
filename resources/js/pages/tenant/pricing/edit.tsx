import { Form } from '@inertiajs/react';
import PricingController from '@/actions/App/Http/Controllers/Tenant/PricingController';
import FormPage from '@/components/form-page';
import TextField from '@/components/text-field';
import { Button } from '@/components/ui/button';

type Discounts = {
    weekly_discount: number;
    monthly_discount: number;
    annual_discount: number;
};

const fields: { name: keyof Discounts; label: string; days: number }[] = [
    { name: 'weekly_discount', label: 'Descuento semanal (%)', days: 7 },
    { name: 'monthly_discount', label: 'Descuento mensual (%)', days: 30 },
    { name: 'annual_discount', label: 'Descuento anual (%)', days: 365 },
];

export default function PricingEdit(discounts: Discounts) {
    return (
        <FormPage title="Precios">
            <div className="mb-6 space-y-2 text-sm text-muted-foreground">
                <p>
                    Cada unidad tiene su tarifa diaria. Las reservas semanales,
                    mensuales y anuales cuestan 7, 30 o 365 días de tarifa menos
                    el descuento que pongas aquí. Los días sueltos se cobran a
                    la tarifa diaria, sin descuento.
                </p>
                <p>
                    Los cambios solo afectan a las reservas nuevas: los
                    contratos existentes mantienen su precio.
                </p>
            </div>

            <Form {...PricingController.update.form()} className="space-y-6">
                {({ processing, errors }) => (
                    <>
                        {fields.map((field) => (
                            <TextField
                                key={field.name}
                                label={`${field.label} · ${field.days} días de tarifa`}
                                name={field.name}
                                type="number"
                                min="0"
                                max="100"
                                step="1"
                                defaultValue={discounts[field.name]}
                                error={errors[field.name]}
                                required
                            />
                        ))}

                        <Button disabled={processing}>
                            Guardar descuentos
                        </Button>
                    </>
                )}
            </Form>
        </FormPage>
    );
}
