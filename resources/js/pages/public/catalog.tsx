import { Head } from '@inertiajs/react';
import { Mail, Phone } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatCents } from '@/lib/format';

type Mode = 'days' | 'weekly' | 'monthly' | 'annual';

type Props = {
    business: { name: string; phone: string | null; email: string | null };
    units: {
        id: number;
        code: string;
        type_label: string;
        size_m2: string;
        prices_cents: Record<Mode, number>;
    }[];
};

const priceRows: { mode: Mode; label: string }[] = [
    { mode: 'days', label: 'Día suelto' },
    { mode: 'weekly', label: 'Semana' },
    { mode: 'monthly', label: 'Mes (30 días)' },
    { mode: 'annual', label: 'Año (365 días)' },
];

function Contact({ phone, email }: Pick<Props['business'], 'phone' | 'email'>) {
    if (!phone && !email) {
        return null;
    }

    return (
        <div className="flex flex-wrap gap-x-6 gap-y-2 text-sm">
            {phone && (
                <a
                    href={`tel:${phone}`}
                    className="inline-flex items-center gap-2 underline-offset-4 hover:underline"
                >
                    <Phone className="size-4" /> {phone}
                </a>
            )}
            {email && (
                <a
                    href={`mailto:${email}`}
                    className="inline-flex items-center gap-2 underline-offset-4 hover:underline"
                >
                    <Mail className="size-4" /> {email}
                </a>
            )}
        </div>
    );
}

/**
 * Public page of a business: its units free today with their prices.
 */
export default function Catalog({ business, units }: Props) {
    return (
        <>
            <Head title={`${business.name} · Unidades disponibles`} />
            <div className="min-h-screen bg-background text-foreground">
                <main className="mx-auto flex max-w-5xl flex-col gap-8 p-6">
                    <header className="space-y-3">
                        <AppLogoIcon className="size-8 fill-current" />
                        <h1 className="text-3xl font-semibold tracking-tight">
                            {business.name}
                        </h1>
                        <p className="text-muted-foreground">
                            Garajes y trasteros disponibles hoy. Contáctanos
                            para reservar.
                        </p>
                        <Contact
                            phone={business.phone}
                            email={business.email}
                        />
                    </header>

                    {units.length === 0 ? (
                        <p className="text-muted-foreground">
                            Ahora mismo no hay unidades libres. Vuelve a
                            consultarlo pronto o contáctanos.
                        </p>
                    ) : (
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {units.map((unit) => (
                                <Card key={unit.id}>
                                    <CardHeader>
                                        <div className="flex items-center justify-between">
                                            <CardTitle>{unit.code}</CardTitle>
                                            <Badge variant="secondary">
                                                {unit.type_label}
                                            </Badge>
                                        </div>
                                        <CardDescription>
                                            {unit.size_m2.replace('.', ',')} m²
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <dl className="grid grid-cols-[1fr_auto] gap-y-1 text-sm">
                                            {priceRows.map(
                                                ({ mode, label }) => (
                                                    <div
                                                        key={mode}
                                                        className="contents"
                                                    >
                                                        <dt className="text-muted-foreground">
                                                            {label}
                                                        </dt>
                                                        <dd className="text-right font-medium tabular-nums">
                                                            {formatCents(
                                                                unit
                                                                    .prices_cents[
                                                                    mode
                                                                ],
                                                            )}
                                                        </dd>
                                                    </div>
                                                ),
                                            )}
                                        </dl>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    )}

                    <footer className="text-xs text-muted-foreground">
                        Precios según la tarifa vigente. Las reservas por
                        semanas, meses o años incluyen el descuento de cada
                        modalidad.
                    </footer>
                </main>
            </div>
        </>
    );
}
