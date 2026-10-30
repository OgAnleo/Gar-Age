import { Form, usePage } from '@inertiajs/react';
import UnitController from '@/actions/App/Http/Controllers/Tenant/UnitController';
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

export default function DeleteUnit({
    unit,
}: {
    unit: { id: number; code: string };
}) {
    const { tenant } = usePage().props;

    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button variant="link" size="sm" className="text-destructive">
                    Eliminar
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogTitle>¿Eliminar la unidad {unit.code}?</DialogTitle>
                <DialogDescription>
                    Esta acción no se puede deshacer.
                </DialogDescription>

                <Form
                    {...UnitController.destroy.form({
                        tenant: tenant?.slug,
                        unit: unit.id,
                    })}
                >
                    {({ processing }) => (
                        <DialogFooter className="gap-2">
                            <DialogClose asChild>
                                <Button type="button" variant="secondary">
                                    Cancelar
                                </Button>
                            </DialogClose>
                            <Button
                                type="submit"
                                variant="destructive"
                                disabled={processing}
                            >
                                Eliminar
                            </Button>
                        </DialogFooter>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
