import { Head, Link, usePage } from '@inertiajs/react';
import { AlertTriangle, Clock, PiggyBank, Warehouse } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import FeeController from '@/actions/App/Http/Controllers/Tenant/FeeController';
import UnitController from '@/actions/App/Http/Controllers/Tenant/UnitController';
import DataTable from '@/components/data-table';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatCents, formatDate, formatMonth } from '@/lib/format';

type Tally = { count: number; amount_cents: number };

type Props = {
    occupancy: { occupied: number; total: number; percent: number };
    revenue: {
        collected_cents: number;
        issued_cents: number;
        months: { month: string; collected_cents: number }[];
    };
    pending: Tally;
    overdue: Tally;
    overdue_fees: {
        id: number;
        unit_code: string;
        customer_name: string;
        amount_cents: number;
        due_on: string;
        days_overdue: number;
    }[];
    free_units: {
        id: number;
        code: string;
        type_label: string;
        daily_price_cents: number;
    }[];
};

type Tone = 'primary' | 'success' | 'warning' | 'danger' | 'muted';

const toneClasses: Record<Tone, string> = {
    primary: 'bg-primary/10 text-primary',
    success: 'bg-success/15 text-success',
    warning: 'bg-warning/15 text-warning',
    danger: 'bg-destructive/10 text-destructive-foreground',
    muted: 'bg-muted text-muted-foreground',
};

function Kpi({
    title,
    value,
    detail,
    icon: Icon,
    tone,
    children,
}: {
    title: string;
    value: string;
    detail: string;
    icon: LucideIcon;
    tone: Tone;
    children?: ReactNode;
}) {
    return (
        <Card className="gap-2">
            <CardHeader className="flex flex-row items-start justify-between gap-2">
                <div className="space-y-1.5">
                    <CardDescription>{title}</CardDescription>
                    <CardTitle className="text-3xl tabular-nums">
                        {value}
                    </CardTitle>
                </div>
                <span
                    className={`flex size-9 shrink-0 items-center justify-center rounded-lg ${toneClasses[tone]}`}
                >
                    <Icon className="size-5" aria-hidden="true" />
                </span>
            </CardHeader>
            <CardContent className="space-y-2 text-sm text-muted-foreground">
                {children}
                <p>{detail}</p>
            </CardContent>
        </Card>
    );
}

function Meter({ percent }: { percent: number }) {
    return (
        <div
            className="h-2 overflow-hidden rounded-full bg-muted"
            role="meter"
            aria-valuenow={percent}
            aria-valuemin={0}
            aria-valuemax={100}
            aria-label="Ocupación"
        >
            <div
                className="h-full rounded-full bg-primary"
                style={{ width: `${percent}%` }}
            />
        </div>
    );
}

function Panel({
    title,
    action,
    className,
    children,
}: {
    title: string;
    action?: ReactNode;
    className?: string;
    children: ReactNode;
}) {
    return (
        <Card className={className}>
            <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle>{title}</CardTitle>
                {action}
            </CardHeader>
            <CardContent>{children}</CardContent>
        </Card>
    );
}

function feesCount(count: number): string {
    return count === 1 ? '1 cuota' : `${count} cuotas`;
}

function RevenueChart({ months }: Props['revenue']) {
    const highest = Math.max(1, ...months.map((m) => m.collected_cents));

    return (
        <div
            className="flex h-48 items-end gap-3"
            role="img"
            aria-label="Cobrado en los últimos meses"
        >
            {months.map(({ month, collected_cents }, index) => (
                <div
                    key={month}
                    className="flex h-full flex-1 flex-col items-center justify-end gap-1"
                    title={`${formatMonth(month)}: ${formatCents(collected_cents)}`}
                >
                    <span className="text-xs text-muted-foreground tabular-nums">
                        {formatCents(collected_cents)}
                    </span>
                    <div
                        className={`w-full rounded-t ${index === months.length - 1 ? 'bg-primary' : 'bg-chart-2/70'}`}
                        style={{
                            height: `${(collected_cents / highest) * 100}%`,
                        }}
                    />
                    <span className="text-xs text-muted-foreground capitalize">
                        {formatMonth(month)}
                    </span>
                </div>
            ))}
        </div>
    );
}

function SeeAll({ href }: { href: Parameters<typeof Link>[0]['href'] }) {
    return (
        <Link href={href} className="text-sm underline underline-offset-4">
            Ver todas
        </Link>
    );
}

export default function TenantDashboard({
    occupancy,
    revenue,
    pending,
    overdue,
    overdue_fees,
    free_units,
}: Props) {
    const { tenant } = usePage().props;
    const freeCount = occupancy.total - occupancy.occupied;

    return (
        <>
            <Head title={tenant?.name ?? 'Panel'} />
            <div className="flex flex-col gap-4 p-4">
                <h1 className="text-2xl font-semibold">{tenant?.name}</h1>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <Kpi
                        title="Ocupación hoy"
                        value={`${occupancy.percent} %`}
                        detail={`${occupancy.occupied} de ${occupancy.total} ${occupancy.total === 1 ? 'unidad ocupada' : 'unidades ocupadas'}`}
                        icon={Warehouse}
                        tone="primary"
                    >
                        <Meter percent={occupancy.percent} />
                    </Kpi>
                    <Kpi
                        title="Cobrado este mes"
                        value={formatCents(revenue.collected_cents)}
                        detail={`Emitido este mes: ${formatCents(revenue.issued_cents)}`}
                        icon={PiggyBank}
                        tone="success"
                    />
                    <Kpi
                        title="Cuotas pendientes"
                        value={formatCents(pending.amount_cents)}
                        detail={`${feesCount(pending.count)} dentro de plazo`}
                        icon={Clock}
                        tone="warning"
                    />
                    <Kpi
                        title="Cuotas vencidas"
                        value={formatCents(overdue.amount_cents)}
                        detail={`${feesCount(overdue.count)} fuera de plazo`}
                        icon={AlertTriangle}
                        tone={overdue.count > 0 ? 'danger' : 'muted'}
                    />
                </div>

                <Panel title="Cobrado en los últimos meses">
                    <RevenueChart {...revenue} />
                </Panel>

                <div className="grid gap-4 xl:grid-cols-5">
                    <Panel
                        className="xl:col-span-3"
                        title="Cuotas vencidas"
                        action={<SeeAll href={FeeController.index()} />}
                    >
                        {overdue_fees.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No hay cuotas vencidas.
                            </p>
                        ) : (
                            <DataTable>
                                <thead className="border-b text-muted-foreground">
                                    <tr>
                                        <th className="py-2">Cliente</th>
                                        <th className="py-2">Unidad</th>
                                        <th className="py-2 pr-6 text-right">
                                            Importe
                                        </th>
                                        <th className="py-2">Venció</th>
                                        <th className="py-2 text-right">
                                            Retraso
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {overdue_fees.map((fee) => (
                                        <tr key={fee.id} className="border-b">
                                            <td className="py-2">
                                                {fee.customer_name}
                                            </td>
                                            <td className="py-2">
                                                {fee.unit_code}
                                            </td>
                                            <td className="py-2 pr-6 text-right">
                                                {formatCents(fee.amount_cents)}
                                            </td>
                                            <td className="py-2">
                                                {formatDate(fee.due_on)}
                                            </td>
                                            <td className="py-2 text-right font-medium text-destructive-foreground">
                                                {fee.days_overdue} d
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </DataTable>
                        )}
                    </Panel>

                    <Panel
                        className="xl:col-span-2"
                        title={`Unidades libres hoy (${freeCount})`}
                        action={<SeeAll href={UnitController.index()} />}
                    >
                        {free_units.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No hay unidades libres hoy.
                            </p>
                        ) : (
                            <DataTable>
                                <thead className="border-b text-muted-foreground">
                                    <tr>
                                        <th className="py-2">Código</th>
                                        <th className="py-2">Tipo</th>
                                        <th className="py-2 text-right">
                                            Tarifa diaria
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {free_units.map((unit) => (
                                        <tr key={unit.id} className="border-b">
                                            <td className="py-2 font-medium">
                                                {unit.code}
                                            </td>
                                            <td className="py-2">
                                                {unit.type_label}
                                            </td>
                                            <td className="py-2 text-right">
                                                {formatCents(
                                                    unit.daily_price_cents,
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </DataTable>
                        )}
                    </Panel>
                </div>
            </div>
        </>
    );
}
