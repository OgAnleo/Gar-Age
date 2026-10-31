import { Form, usePage } from '@inertiajs/react';
import BusinessController from '@/actions/App/Http/Controllers/Tenant/BusinessController';
import FormPage from '@/components/form-page';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

export default function BusinessEdit() {
    const { tenant } = usePage().props;

    return (
        <FormPage title="Negocio">
            <dl className="mb-8 grid grid-cols-[auto_1fr] gap-x-6 gap-y-2 text-sm">
                <dt className="text-muted-foreground">Nombre</dt>
                <dd className="font-medium">{tenant?.name}</dd>
                <dt className="text-muted-foreground">Dirección</dt>
                <dd className="font-medium">/{tenant?.slug}</dd>
            </dl>

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
