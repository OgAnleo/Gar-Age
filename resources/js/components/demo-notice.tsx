import { usePage } from '@inertiajs/react';
import { Info } from 'lucide-react';

function Credential({ children }: { children: string }) {
    return <span className="font-medium text-foreground">{children}</span>;
}

/**
 * On the public demo: the credentials to try it (login page) or a reminder
 * that the demo accounts are shared and rebuilt every night (inside the app).
 */
export default function DemoNotice({ variant }: { variant: 'login' | 'app' }) {
    const { demo } = usePage().props;

    if (!demo || (variant === 'app' && !demo.isDemoUser)) {
        return null;
    }

    return (
        <div className="flex items-start gap-2 rounded-lg border bg-muted/50 p-3 text-sm text-muted-foreground">
            <Info className="mt-0.5 size-4 shrink-0" />
            {variant === 'login' ? (
                <div className="space-y-1">
                    <p>
                        Demo, con la contraseña{' '}
                        <Credential>{demo.password}</Credential>:
                    </p>
                    <ul className="list-inside list-disc">
                        <li>
                            Dueño: <Credential>{demo.email}</Credential>
                        </li>
                        <li>
                            Empleado: <Credential>{demo.staffEmail}</Credential>
                        </li>
                    </ul>
                </div>
            ) : (
                <p>
                    Cuenta de demostración compartida: los datos se reinician
                    cada noche y no se pueden cambiar sus credenciales, el
                    equipo ni borrar el negocio.
                </p>
            )}
        </div>
    );
}
