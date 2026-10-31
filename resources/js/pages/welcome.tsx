import { Head, Link, usePage } from '@inertiajs/react';
import { CalendarDays, Gauge, Receipt, Warehouse } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { dashboard, login, register } from '@/routes';

const features = [
    {
        icon: Warehouse,
        title: 'Unidades',
        text: 'Garajes y trasteros con su superficie y tarifa diaria.',
    },
    {
        icon: CalendarDays,
        title: 'Reservas flexibles',
        text: 'Por semanas, meses, años o días sueltos, sin solapes.',
    },
    {
        icon: Receipt,
        title: 'Cuotas automáticas',
        text: 'Se emiten al empezar cada periodo y avisan de los impagos.',
    },
    {
        icon: Gauge,
        title: 'Panel',
        text: 'Ocupación, cobros del mes y cuotas vencidas de un vistazo.',
    },
];

export default function Welcome() {
    const { auth, name } = usePage().props;

    return (
        <>
            <Head title="Alquiler de garajes y trasteros" />
            <div className="flex min-h-screen flex-col bg-background text-foreground">
                <header className="mx-auto flex w-full max-w-5xl items-center justify-between p-6">
                    <div className="flex items-center gap-2 font-semibold">
                        <AppLogoIcon className="size-7 fill-current" />
                        {name}
                    </div>
                    <nav className="flex gap-2">
                        {auth.user ? (
                            <Button asChild>
                                <Link href={dashboard()}>Ir al panel</Link>
                            </Button>
                        ) : (
                            <>
                                <Button variant="ghost" asChild>
                                    <Link href={login()}>Entrar</Link>
                                </Button>
                                <Button asChild>
                                    <Link href={register()}>Crear cuenta</Link>
                                </Button>
                            </>
                        )}
                    </nav>
                </header>

                <main className="mx-auto flex w-full max-w-5xl flex-1 flex-col justify-center gap-12 p-6">
                    <section className="max-w-2xl space-y-4">
                        <h1 className="text-4xl font-semibold tracking-tight sm:text-5xl">
                            Gestiona el alquiler de tus garajes y trasteros
                        </h1>
                        <p className="text-lg text-muted-foreground">
                            Unidades, clientes, contratos y cobros en un solo
                            sitio, para cada negocio por separado.
                        </p>
                    </section>

                    <section className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        {features.map(({ icon: Icon, title, text }) => (
                            <div key={title} className="space-y-2">
                                <Icon className="size-5 text-muted-foreground" />
                                <h2 className="font-medium">{title}</h2>
                                <p className="text-sm text-muted-foreground">
                                    {text}
                                </p>
                            </div>
                        ))}
                    </section>
                </main>
            </div>
        </>
    );
}
