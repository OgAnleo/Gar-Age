import { Link } from '@inertiajs/react';
import ContractController from '@/actions/App/Http/Controllers/Tenant/ContractController';
import ConfirmDelete from '@/components/confirm-delete';
import {
    type BookingMode,
    pricePeriodLabel,
} from '@/components/contracts/booking';
import DataTable from '@/components/data-table';
import ResourceIndex from '@/components/resource-index';
import { Badge } from '@/components/ui/badge';
import { formatCents, formatDate } from '@/lib/format';

type Contract = {
    id: number;
    unit_code: string;
    customer_name: string;
    mode: BookingMode;
    mode_label: string;
    starts_on: string;
    ends_on: string | null;
    days_count: number | null;
    price_cents: number;
    status: 'upcoming' | 'active' | 'finished';
    status_label: string;
};

const statusVariant = {
    upcoming: 'outline',
    active: 'default',
    finished: 'secondary',
} as const;

function Dates({ contract }: { contract: Contract }) {
    const start = formatDate(contract.starts_on);

    if (contract.days_count !== null) {
        return (
            <>
                {contract.days_count} días, del {start} al{' '}
                {formatDate(contract.ends_on ?? contract.starts_on)}
            </>
        );
    }

    return contract.ends_on ? (
        <>
            {start} – {formatDate(contract.ends_on)}
        </>
    ) : (
        <>Desde {start}, indefinido</>
    );
}

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
            <DataTable>
                <thead className="border-b text-muted-foreground">
                    <tr>
                        <th className="py-2">Unidad</th>
                        <th className="py-2">Cliente</th>
                        <th className="py-2">Modalidad</th>
                        <th className="py-2">Fechas</th>
                        <th className="py-2 pr-6 text-right">Precio</th>
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
                            <td className="py-2">{contract.mode_label}</td>
                            <td className="py-2">
                                <Dates contract={contract} />
                            </td>
                            <td className="py-2 pr-6 text-right whitespace-nowrap">
                                {formatCents(contract.price_cents)}{' '}
                                {pricePeriodLabel[contract.mode]}
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
            </DataTable>
        </ResourceIndex>
    );
}
