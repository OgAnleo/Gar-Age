import { Link } from '@inertiajs/react';
import ContractController from '@/actions/App/Http/Controllers/Tenant/ContractController';
import ConfirmDelete from '@/components/confirm-delete';
import ResourceIndex from '@/components/resource-index';
import { Badge } from '@/components/ui/badge';
import { formatCents, formatDate } from '@/lib/format';

type Contract = {
    id: number;
    unit_code: string;
    customer_name: string;
    starts_on: string;
    ends_on: string | null;
    monthly_price_cents: number;
    status: 'upcoming' | 'active' | 'finished';
    status_label: string;
};

const statusVariant = {
    upcoming: 'outline',
    active: 'default',
    finished: 'secondary',
} as const;

export default function ContractsIndex({
    contracts,
}: {
    contracts: Contract[];
}) {
    return (
        <ResourceIndex
            title="Contratos"
            createHref={ContractController.create()}
            createLabel="Nuevo contrato"
            emptyText="Todavía no hay contratos."
            isEmpty={contracts.length === 0}
        >
            <table className="w-full text-left text-sm">
                <thead className="border-b text-muted-foreground">
                    <tr>
                        <th className="py-2">Unidad</th>
                        <th className="py-2">Cliente</th>
                        <th className="py-2">Inicio</th>
                        <th className="py-2">Fin</th>
                        <th className="py-2 text-right">Precio/mes</th>
                        <th className="py-2">Estado</th>
                        <th className="py-2" />
                    </tr>
                </thead>
                <tbody>
                    {contracts.map((contract) => (
                        <tr key={contract.id} className="border-b">
                            <td className="py-2 font-medium">
                                {contract.unit_code}
                            </td>
                            <td className="py-2">{contract.customer_name}</td>
                            <td className="py-2">
                                {formatDate(contract.starts_on)}
                            </td>
                            <td className="py-2">
                                {contract.ends_on
                                    ? formatDate(contract.ends_on)
                                    : 'Indefinido'}
                            </td>
                            <td className="py-2 text-right">
                                {formatCents(contract.monthly_price_cents)}
                            </td>
                            <td className="py-2">
                                <Badge variant={statusVariant[contract.status]}>
                                    {contract.status_label}
                                </Badge>
                            </td>
                            <td className="py-2">
                                <div className="flex items-center justify-end gap-2">
                                    <Link
                                        href={ContractController.edit({
                                            contract: contract.id,
                                        })}
                                        className="text-sm underline underline-offset-4"
                                    >
                                        Editar
                                    </Link>
                                    <ConfirmDelete
                                        title={`¿Eliminar el contrato de ${contract.unit_code}?`}
                                        action={ContractController.destroy.form(
                                            { contract: contract.id },
                                        )}
                                    />
                                </div>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </ResourceIndex>
    );
}
