import { Form } from '@inertiajs/react';
import { useState } from 'react';
import FeeController from '@/actions/App/Http/Controllers/Tenant/FeeController';
import DataTable from '@/components/data-table';
import ResourceIndex from '@/components/resource-index';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { formatCents, formatDate } from '@/lib/format';

type FeeStatus = 'pending' | 'overdue' | 'paid';

type Fee = {
    id: number;
    period_start: string;
    period_end: string;
    due_on: string;
    unit_code: string;
    customer_name: string;
    mode_label: string;
    amount_cents: number;
    paid_on: string | null;
    status: FeeStatus;
    status_label: string;
};

type Filter = 'all' | FeeStatus;

const filters: { value: Filter; label: string }[] = [
    { value: 'all', label: 'Todas' },
    { value: 'paid', label: 'Pagadas' },
    { value: 'pending', label: 'Pendientes' },
    { value: 'overdue', label: 'Vencidas' },
];

const statusVariant = {
    pending: 'outline',
    overdue: 'destructive',
    paid: 'secondary',
} as const;

function PaymentButton({ fee }: { fee: Fee }) {
    const paid = fee.paid_on !== null;
    const action = paid
        ? FeeController.unpay.form({ fee: fee.id })
        : FeeController.pay.form({ fee: fee.id });

    return (
        <Form {...action}>
            {({ processing }) => (
                <Button
                    type="submit"
                    size="sm"
                    variant={paid ? 'ghost' : 'outline'}
                    disabled={processing}
                >
                    {paid ? 'Deshacer pago' : 'Marcar pagada'}
                </Button>
            )}
        </Form>
    );
}

export default function FeesIndex({ fees }: { fees: Fee[] }) {
    const [filter, setFilter] = useState<Filter>('all');
    const visibleFees =
        filter === 'all' ? fees : fees.filter((fee) => fee.status === filter);
    const countOf = (value: Filter) =>
        value === 'all'
            ? fees.length
            : fees.filter((fee) => fee.status === value).length;

    return (
        <ResourceIndex
            title="Cuotas"
            emptyText="Todavía no hay cuotas. Se emiten cada día al empezar cada periodo de los contratos, o puedes emitirlas ahora."
            isEmpty={fees.length === 0}
            actions={
                <Form {...FeeController.generate.form()}>
                    {({ processing }) => (
                        <Button disabled={processing}>
                            Emitir cuotas pendientes
                        </Button>
                    )}
                </Form>
            }
        >
            <ToggleGroup
                type="single"
                variant="outline"
                value={filter}
                // Radix sends "" when the active item is clicked again: keep the current filter.
                onValueChange={(value: Filter | '') =>
                    value && setFilter(value)
                }
                className="max-w-full self-start overflow-x-auto"
                aria-label="Filtrar cuotas por estado"
            >
                {filters.map(({ value, label }) => (
                    <ToggleGroupItem key={value} value={value} className="px-3">
                        {label} ({countOf(value)})
                    </ToggleGroupItem>
                ))}
            </ToggleGroup>

            {visibleFees.length === 0 ? (
                <p className="text-sm text-muted-foreground">
                    No hay cuotas con este estado.
                </p>
            ) : (
                <DataTable>
                    <thead className="border-b text-muted-foreground">
                        <tr>
                            <th className="py-2">Periodo</th>
                            <th className="py-2">Unidad</th>
                            <th className="py-2">Cliente</th>
                            <th className="py-2">Modalidad</th>
                            <th className="py-2 pr-6 text-right">Importe</th>
                            <th className="py-2">Vence</th>
                            <th className="py-2">Estado</th>
                            <th className="py-2">Pagada el</th>
                            <th className="py-2" />
                        </tr>
                    </thead>
                    <tbody>
                        {visibleFees.map((fee) => (
                            <tr key={fee.id} className="border-b">
                                <td className="py-2 font-medium whitespace-nowrap">
                                    {formatDate(fee.period_start)} –{' '}
                                    {formatDate(fee.period_end)}
                                </td>
                                <td className="py-2">{fee.unit_code}</td>
                                <td className="py-2">{fee.customer_name}</td>
                                <td className="py-2">{fee.mode_label}</td>
                                <td className="py-2 pr-6 text-right">
                                    {formatCents(fee.amount_cents)}
                                </td>
                                <td className="py-2">
                                    {formatDate(fee.due_on)}
                                </td>
                                <td className="py-2">
                                    <Badge variant={statusVariant[fee.status]}>
                                        {fee.status_label}
                                    </Badge>
                                </td>
                                <td className="py-2">
                                    {fee.paid_on
                                        ? formatDate(fee.paid_on)
                                        : '—'}
                                </td>
                                <td className="py-2 text-right">
                                    <PaymentButton fee={fee} />
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </DataTable>
            )}
        </ResourceIndex>
    );
}
