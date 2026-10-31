import { Link } from '@inertiajs/react';
import UnitController from '@/actions/App/Http/Controllers/Tenant/UnitController';
import ConfirmDelete from '@/components/confirm-delete';
import ResourceIndex from '@/components/resource-index';

type Unit = {
    id: number;
    code: string;
    type: string;
    type_label: string;
    size_m2: string;
    monthly_price_cents: number;
};

const euros = new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'EUR',
});

export default function UnitsIndex({ units }: { units: Unit[] }) {
    return (
        <ResourceIndex
            title="Unidades"
            createHref={UnitController.create()}
            createLabel="Nueva unidad"
            emptyText="Todavía no hay unidades."
            isEmpty={units.length === 0}
        >
            <table className="w-full text-left text-sm">
                <thead className="border-b text-muted-foreground">
                    <tr>
                        <th className="py-2">Código</th>
                        <th className="py-2">Tipo</th>
                        <th className="py-2 text-right">m²</th>
                        <th className="py-2 text-right">Precio/mes</th>
                        <th className="py-2" />
                    </tr>
                </thead>
                <tbody>
                    {units.map((unit) => (
                        <tr key={unit.id} className="border-b">
                            <td className="py-2 font-medium">{unit.code}</td>
                            <td className="py-2">{unit.type_label}</td>
                            <td className="py-2 text-right">{unit.size_m2}</td>
                            <td className="py-2 text-right">
                                {euros.format(unit.monthly_price_cents / 100)}
                            </td>
                            <td className="py-2">
                                <div className="flex items-center justify-end gap-2">
                                    <Link
                                        href={UnitController.edit({
                                            unit: unit.id,
                                        })}
                                        className="text-sm underline underline-offset-4"
                                    >
                                        Editar
                                    </Link>
                                    <ConfirmDelete
                                        title={`¿Eliminar la unidad ${unit.code}?`}
                                        action={UnitController.destroy.form({
                                            unit: unit.id,
                                        })}
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
