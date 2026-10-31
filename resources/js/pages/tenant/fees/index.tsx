import { Form } from '@inertiajs/react';
import FeeController from '@/actions/App/Http/Controllers/Tenant/FeeController';
import ResourceIndex from '@/components/resource-index';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatCents, formatDate, formatMonth } from '@/lib/format';

type Fee = {
    id: number;
    month: string;
    unit_code: string;
    customer_name: string;
    amount_cents: number;
    paid_on: string | null;
    status: 'pending' | 'overdue' | 'paid';
    status_label: string;
};

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

export default function FeesIndex({
    fees,
    current_month,
}: {
    fees: Fee[];
    current_month: string;
}) {
    return (
        <ResourceIndex
            title="Cuotas"
            emptyText="Todavía no hay cuotas. Se generan cada día para los contratos en curso, o puedes generarlas ahora."
            isEmpty={fees.length === 0}
            actions={
                <Form {...FeeController.generate.form()}>
                    {({ processing }) => (
                        <Button disabled={processing}>
                            Generar cuotas de {formatMonth(current_month)}
                        </Button>
                    )}
                </Form>
            }
        >
            <table className="w-full text-left text-sm">
                <thead className="border-b text-muted-foreground">
                    <tr>
                        <th className="py-2">Mes</th>
                        <th className="py-2">Unidad</th>
                        <th className="py-2">Cliente</th>
                        <th className="py-2 pr-6 text-right">Importe</th>
                        <th className="py-2">Estado</th>
                        <th className="py-2">Pagada el</th>
                        <th className="py-2" />
                    </tr>
                </thead>
                <tbody>
                    {fees.map((fee) => (
                        <tr key={fee.id} className="border-b">
                            <td className="py-2 font-medium first-letter:uppercase">
                                {formatMonth(fee.month)}
                            </td>
                            <td className="py-2">{fee.unit_code}</td>
                            <td className="py-2">{fee.customer_name}</td>
                            <td className="py-2 pr-6 text-right">
                                {formatCents(fee.amount_cents)}
                            </td>
                            <td className="py-2">
                                <Badge variant={statusVariant[fee.status]}>
                                    {fee.status_label}
                                </Badge>
                            </td>
                            <td className="py-2">
                                {fee.paid_on ? formatDate(fee.paid_on) : '—'}
                            </td>
                            <td className="py-2 text-right">
                                <PaymentButton fee={fee} />
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </ResourceIndex>
    );
}
