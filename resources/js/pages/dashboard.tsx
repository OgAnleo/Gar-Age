import { Form } from '@inertiajs/react';
import NewBusinessController from '@/actions/App/Http/Controllers/NewBusinessController';
import FormPage from '@/components/form-page';
import TextField from '@/components/text-field';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';

/**
 * Landing page of a user without a business (e.g. after deleting theirs).
 */
export default function Dashboard() {
    return (
        <FormPage title="Crea tu negocio">
            <p className="mb-6 text-sm text-muted-foreground">
                Todavía no tienes ningún negocio. Créalo para gestionar sus
                unidades, clientes, contratos y cuotas.
            </p>

            <Form {...NewBusinessController.form()} className="space-y-6">
                {({ processing, errors }) => (
                    <>
                        <TextField
                            name="business_name"
                            label="Nombre del negocio"
                            error={errors.business_name}
                            required
                        />
                        <Button disabled={processing}>Crear negocio</Button>
                    </>
                )}
            </Form>
        </FormPage>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Inicio',
            href: dashboard(),
        },
    ],
};
