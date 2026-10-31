import { Link } from '@inertiajs/react';
import CustomerController from '@/actions/App/Http/Controllers/Tenant/CustomerController';
import ConfirmDelete from '@/components/confirm-delete';
import type { CustomerFormValues } from '@/components/customers/customer-form';
import DataTable from '@/components/data-table';
import ResourceIndex from '@/components/resource-index';

type Customer = CustomerFormValues & { id: number };

export default function CustomersIndex({
    customers,
}: {
    customers: Customer[];
}) {
    return (
        <ResourceIndex
            title="Clientes"
            createHref={CustomerController.create()}
            createLabel="Nuevo cliente"
            emptyText="Todavía no hay clientes."
            isEmpty={customers.length === 0}
        >
            <DataTable>
                <thead className="border-b text-muted-foreground">
                    <tr>
                        <th className="py-2">Nombre</th>
                        <th className="py-2">DNI/NIF</th>
                        <th className="py-2">Email</th>
                        <th className="py-2">Teléfono</th>
                        <th className="py-2" />
                    </tr>
                </thead>
                <tbody>
                    {customers.map((customer) => (
                        <tr key={customer.id} className="border-b">
                            <td className="py-2 font-medium">
                                {customer.name}
                            </td>
                            <td className="py-2">{customer.tax_id}</td>
                            <td className="py-2">{customer.email ?? '—'}</td>
                            <td className="py-2">{customer.phone ?? '—'}</td>
                            <td className="py-2">
                                <div className="flex items-center justify-end gap-2">
                                    <Link
                                        href={CustomerController.edit({
                                            customer: customer.id,
                                        })}
                                        className="text-sm underline underline-offset-4"
                                    >
                                        Editar
                                    </Link>
                                    <ConfirmDelete
                                        title={`¿Eliminar a ${customer.name}?`}
                                        action={CustomerController.destroy.form(
                                            { customer: customer.id },
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
