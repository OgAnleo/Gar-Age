import { Form, usePage } from '@inertiajs/react';
import { useState } from 'react';
import BusinessController from '@/actions/App/Http/Controllers/Tenant/BusinessController';
import FormPage from '@/components/form-page';
import Heading from '@/components/heading';
import TextField from '@/components/text-field';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

type PublicPage = {
    enabled: boolean;
    contact_phone: string;
    contact_email: string;
    url: string;
};

/**
 * Publish the page with the free units, and how visitors contact the business.
 */
function PublicPageForm({ page }: { page: PublicPage }) {
    const [enabled, setEnabled] = useState(page.enabled);

    return (
        <section className="mb-8 space-y-4">
            <Heading
                variant="small"
                title="Página pública"
                description="Muestra a cualquiera, sin cuenta, las unidades libres hoy con sus precios por día, semana, mes y año."
            />
            <Form
                {...BusinessController.updatePublicPage.form()}
                options={{ preserveScroll: true }}
                className="space-y-4"
            >
                {({ processing, errors }) => (
                    <>
                        <input
                            type="hidden"
                            name="public_page_enabled"
                            value={enabled ? '1' : '0'}
                        />
                        <div className="flex items-center gap-3">
                            <Checkbox
                                id="public_page_enabled"
                                checked={enabled}
                                onCheckedChange={(checked) =>
                                    setEnabled(checked === true)
                                }
                            />
                            <Label htmlFor="public_page_enabled">
                                Publicar la página
                            </Label>
                        </div>
                        <TextField
                            name="contact_phone"
                            label="Teléfono de contacto"
                            defaultValue={page.contact_phone}
                            error={errors.contact_phone}
                        />
                        <TextField
                            name="contact_email"
                            type="email"
                            label="Email de contacto"
                            defaultValue={page.contact_email}
                            error={errors.contact_email}
                        />
                        <div className="flex flex-wrap items-center gap-4">
                            <Button disabled={processing}>Guardar</Button>
                            {page.enabled && (
                                <a
                                    href={page.url}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="text-sm underline underline-offset-4"
                                >
                                    Ver la página pública
                                </a>
                            )}
                        </div>
                    </>
                )}
            </Form>
        </section>
    );
}

export default function BusinessEdit({
    public_page,
}: {
    public_page: PublicPage;
}) {
    const { tenant } = usePage().props;

    return (
        <FormPage title="Negocio">
            <dl className="mb-8 grid grid-cols-[auto_1fr] gap-x-6 gap-y-2 text-sm">
                <dt className="text-muted-foreground">Nombre</dt>
                <dd className="font-medium">{tenant?.name}</dd>
                <dt className="text-muted-foreground">Dirección</dt>
                <dd className="font-medium">/{tenant?.slug}</dd>
            </dl>

            <PublicPageForm page={public_page} />

            <div className="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
                <div className="space-y-0.5 text-red-600 dark:text-red-100">
                    <p className="font-medium">Eliminar el negocio</p>
                    <p className="text-sm">
                        Se borrarán sus unidades, clientes, contratos y cuotas.
                        Esta acción no se puede deshacer.
                    </p>
                </div>

                <Dialog>
                    <DialogTrigger asChild>
                        <Button variant="destructive">Eliminar negocio</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>
                            ¿Eliminar {tenant?.name} y todos sus datos?
                        </DialogTitle>
                        <DialogDescription>
                            Se borrarán sus unidades, clientes, contratos y
                            cuotas. Esta acción no se puede deshacer.
                        </DialogDescription>

                        <Form {...BusinessController.destroy.form()}>
                            {({ processing }) => (
                                <DialogFooter className="gap-2">
                                    <DialogClose asChild>
                                        <Button
                                            type="button"
                                            variant="secondary"
                                        >
                                            Cancelar
                                        </Button>
                                    </DialogClose>
                                    <Button
                                        type="submit"
                                        variant="destructive"
                                        disabled={processing}
                                    >
                                        Eliminar negocio
                                    </Button>
                                </DialogFooter>
                            )}
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>
        </FormPage>
    );
}
