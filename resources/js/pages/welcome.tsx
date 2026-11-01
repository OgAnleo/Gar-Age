import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    CalendarDays,
    Gauge,
    Globe,
    Receipt,
    Users,
    Warehouse,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { dashboard, login, register } from '@/routes';

const features: { icon: LucideIcon; title: string; text: string }[] = [
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
    {
        icon: Users,
        title: 'Equipo',
        text: 'Empleados que gestionan el día a día sin tocar precios ni ajustes.',
    },
    {
        icon: Globe,
        title: 'Página pública',
        text: 'Tus unidades libres con sus precios, para que te contacten.',
    },
];

const steps = [
    {
        title: 'Crea tu negocio',
        text: 'Regístrate y da de alta tus garajes y trasteros con su tarifa diaria.',
    },
    {
        title: 'Alquila',
        text: 'Registra clientes y contratos; la app evita reservar dos veces la misma unidad.',
    },
    {
        title: 'Cobra',
        text: 'Las cuotas se emiten solas y ves de un vistazo qué está pendiente o vencido.',
    },
];

const previewBars = [38, 52, 64, 88, 72, 95];

/**
 * A stylised miniature of the dashboard, drawn with HTML (no screenshot).
 */
function DashboardPreview() {
    return (
        <div
            className="rounded-2xl border bg-card p-4 shadow-xl shadow-primary/10"
            aria-hidden="true"
        >
            <div className="mb-4 flex items-center gap-2">
                <span className="size-2.5 rounded-full bg-destructive/60" />
                <span className="size-2.5 rounded-full bg-warning/70" />
                <span className="size-2.5 rounded-full bg-success/70" />
                <span className="ml-2 text-xs text-muted-foreground">
                    Trasteros Demo · Panel
                </span>
            </div>
            <div className="grid grid-cols-2 gap-3">
                <div className="rounded-lg border p-3">
                    <p className="text-xs text-muted-foreground">
                        Ocupación hoy
                    </p>
                    <p className="text-xl font-semibold">58 %</p>
                    <div className="mt-2 h-1.5 rounded-full bg-muted">
                        <div className="h-full w-[58%] rounded-full bg-primary" />
                    </div>
                </div>
                <div className="rounded-lg border p-3">
                    <p className="text-xs text-muted-foreground">
                        Cobrado este mes
                    </p>
                    <p className="text-xl font-semibold text-success">
                        431,26 €
                    </p>
                    <p className="mt-1 text-xs text-muted-foreground">
                        2 cuotas vencidas
                    </p>
                </div>
            </div>
            <div className="mt-3 flex h-28 items-end gap-2 rounded-lg border p-3">
                {previewBars.map((height, index) => (
                    <div
                        key={index}
                        className={`flex-1 rounded-t ${index === previewBars.length - 1 ? 'bg-primary' : 'bg-chart-2/70'}`}
                        style={{ height: `${height}%` }}
                    />
                ))}
            </div>
        </div>
    );
}

export default function Welcome() {
    const { auth, name, demo } = usePage().props;

    return (
        <>
            <Head title="Alquiler de garajes y trasteros" />
            <div className="flex min-h-screen flex-col bg-background text-foreground">
                <header className="mx-auto flex w-full max-w-6xl items-center justify-between p-4 sm:p-6">
                    <div className="flex items-center gap-2 font-semibold">
                        <span className="flex size-8 items-center justify-center rounded-md bg-primary text-primary-foreground">
                            <AppLogoIcon className="size-5 fill-current" />
                        </span>
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

                <main className="flex-1">
                    <section className="bg-gradient-to-b from-accent/60 to-background">
                        <div className="mx-auto grid max-w-6xl items-center gap-12 px-4 py-12 sm:px-6 lg:grid-cols-2 lg:py-20">
                            <div className="space-y-6">
                                <p className="inline-flex rounded-full border bg-card px-3 py-1 text-xs font-medium text-muted-foreground">
                                    Para negocios de alquiler de garajes y
                                    trasteros
                                </p>
                                <h1 className="text-4xl font-semibold tracking-tight sm:text-5xl">
                                    Gestiona tus alquileres{' '}
                                    <span className="text-primary">
                                        sin hojas de cálculo
                                    </span>
                                </h1>
                                <p className="max-w-xl text-lg text-muted-foreground">
                                    Unidades, clientes, contratos y cobros en un
                                    solo sitio, con cuotas que se emiten solas y
                                    un panel que te dice cómo va el negocio.
                                </p>
                                <div className="flex flex-wrap gap-3">
                                    {demo ? (
                                        <Button size="lg" asChild>
                                            <Link href={login()}>
                                                Probar la demo <ArrowRight />
                                            </Link>
                                        </Button>
                                    ) : (
                                        <Button size="lg" asChild>
                                            <Link href={register()}>
                                                Empezar gratis <ArrowRight />
                                            </Link>
                                        </Button>
                                    )}
                                    {demo && (
                                        <Button
                                            size="lg"
                                            variant="outline"
                                            asChild
                                        >
                                            <a href={demo.catalogUrl}>
                                                Ver una página pública
                                            </a>
                                        </Button>
                                    )}
                                </div>
                            </div>
                            <DashboardPreview />
                        </div>
                    </section>

                    <section className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                        <h2 className="mb-8 text-2xl font-semibold tracking-tight">
                            Todo lo que necesita tu negocio
                        </h2>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {features.map(({ icon: Icon, title, text }) => (
                                <div
                                    key={title}
                                    className="space-y-3 rounded-xl border bg-card p-5"
                                >
                                    <span className="flex size-10 items-center justify-center rounded-lg bg-accent text-accent-foreground">
                                        <Icon className="size-5" />
                                    </span>
                                    <h3 className="font-medium">{title}</h3>
                                    <p className="text-sm text-muted-foreground">
                                        {text}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </section>

                    <section className="border-y bg-muted/40">
                        <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                            <h2 className="mb-8 text-2xl font-semibold tracking-tight">
                                Cómo funciona
                            </h2>
                            <ol className="grid gap-6 md:grid-cols-3">
                                {steps.map((step, index) => (
                                    <li key={step.title} className="flex gap-4">
                                        <span className="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary font-semibold text-primary-foreground">
                                            {index + 1}
                                        </span>
                                        <div className="space-y-1">
                                            <h3 className="font-medium">
                                                {step.title}
                                            </h3>
                                            <p className="text-sm text-muted-foreground">
                                                {step.text}
                                            </p>
                                        </div>
                                    </li>
                                ))}
                            </ol>
                        </div>
                    </section>
                </main>

                <footer className="mx-auto flex w-full max-w-6xl flex-wrap items-center justify-between gap-2 p-6 text-sm text-muted-foreground">
                    <span>{name} · Proyecto de portfolio</span>
                    <a
                        href="https://github.com/OgAnleo/Gar-Age"
                        className="underline-offset-4 hover:underline"
                        target="_blank"
                        rel="noreferrer"
                    >
                        Código en GitHub
                    </a>
                </footer>
            </div>
        </>
    );
}
