import { usePage } from '@inertiajs/react';
import { Info } from 'lucide-react';

/**
 * On the public demo: the credentials to try it (login page) or a reminder
 * that the demo account is shared and rebuilt every night (inside the app).
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
                <p>
                    Demo: entra con{' '}
                    <span className="font-medium text-foreground">
                        {demo.email}
                    </span>{' '}
                    y la contraseña{' '}
                    <span className="font-medium text-foreground">
                        {demo.password}
                    </span>
                    .
                </p>
            ) : (
                <p>
                    Cuenta de demostración compartida: los datos se reinician
                    cada noche y no se pueden cambiar sus credenciales ni borrar
                    el negocio.
                </p>
            )}
        </div>
    );
}
