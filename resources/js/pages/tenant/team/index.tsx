import { Form, Head, usePage } from '@inertiajs/react';
import TeamController from '@/actions/App/Http/Controllers/Tenant/TeamController';
import ConfirmDelete from '@/components/confirm-delete';
import DataTable from '@/components/data-table';
import Heading from '@/components/heading';
import TextField from '@/components/text-field';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

type Member = {
    id: number;
    name: string;
    email: string;
    role: 'owner' | 'staff';
    role_label: string;
};

type TemporaryPassword = { email: string; password: string };

/**
 * Shown once, right after adding someone who had no account.
 */
function NewAccountNotice() {
    const { flash } = usePage();
    const account = (flash as { temporaryPassword?: TemporaryPassword })
        .temporaryPassword;

    if (!account) {
        return null;
    }

    return (
        <Alert>
            <AlertTitle>Cuenta creada para {account.email}</AlertTitle>
            <AlertDescription>
                <p>
                    Contraseña temporal:{' '}
                    <code className="rounded bg-muted px-1.5 py-0.5 font-mono text-foreground">
                        {account.password}
                    </code>
                </p>
                <p>
                    Dásela al empleado: no se volverá a mostrar. Podrá cambiarla
                    en Ajustes → Seguridad.
                </p>
            </AlertDescription>
        </Alert>
    );
}

export default function TeamIndex({ members }: { members: Member[] }) {
    return (
        <>
            <Head title="Equipo" />
            <div className="flex max-w-3xl flex-col gap-6 p-4">
                <h1 className="text-2xl font-semibold">Equipo</h1>

                <NewAccountNotice />

                <DataTable>
                    <thead className="border-b text-muted-foreground">
                        <tr>
                            <th className="py-2">Nombre</th>
                            <th className="py-2">Email</th>
                            <th className="py-2">Rol</th>
                            <th className="py-2" />
                        </tr>
                    </thead>
                    <tbody>
                        {members.map((member) => (
                            <tr key={member.id} className="border-b">
                                <td className="py-2 font-medium">
                                    {member.name}
                                </td>
                                <td className="py-2">{member.email}</td>
                                <td className="py-2">
                                    <Badge
                                        variant={
                                            member.role === 'owner'
                                                ? 'secondary'
                                                : 'outline'
                                        }
                                    >
                                        {member.role_label}
                                    </Badge>
                                </td>
                                <td className="py-2 text-right">
                                    {member.role === 'staff' && (
                                        <ConfirmDelete
                                            title={`¿Quitar a ${member.name} del negocio?`}
                                            action={TeamController.destroy.form(
                                                { member: member.id },
                                            )}
                                        />
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </DataTable>

                <section className="space-y-4">
                    <Heading
                        variant="small"
                        title="Añadir empleado"
                        description="Los empleados gestionan unidades, clientes, contratos y cuotas, pero no los precios, el equipo ni el negocio. Si aún no tienen cuenta, se crea con una contraseña temporal."
                    />
                    <Form
                        {...TeamController.store.form()}
                        resetOnSuccess
                        className="grid gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-start"
                    >
                        {({ processing, errors }) => (
                            <>
                                <TextField
                                    name="name"
                                    label="Nombre"
                                    error={errors.name}
                                    required
                                />
                                <TextField
                                    name="email"
                                    type="email"
                                    label="Email"
                                    error={errors.email}
                                    required
                                />
                                <Button
                                    disabled={processing}
                                    className="sm:mt-6"
                                >
                                    Añadir
                                </Button>
                            </>
                        )}
                    </Form>
                </section>
            </div>
        </>
    );
}
