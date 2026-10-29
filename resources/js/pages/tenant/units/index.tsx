import { Head, Link, usePage } from '@inertiajs/react';
import UnitController from '@/actions/App/Http/Controllers/Tenant/UnitController';
import { Button } from '@/components/ui/button';

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
    const { tenant } = usePage().props;

    return (
        <>
            <Head title="Unidades" />
            <div className="flex flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Unidades</h1>
                    <Button asChild>
                        <Link href={UnitController.create(tenant?.slug)}>
                            Nueva unidad
                        </Link>
                    </Button>
                </div>

                {units.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Todavía no hay unidades.
                    </p>
                ) : (
                    <table className="w-full text-left text-sm">
                        <thead className="border-b text-muted-foreground">
                            <tr>
                                <th className="py-2">Código</th>
                                <th className="py-2">Tipo</th>
                                <th className="py-2 text-right">m²</th>
                                <th className="py-2 text-right">Precio/mes</th>
                            </tr>
                        </thead>
                        <tbody>
                            {units.map((unit) => (
                                <tr key={unit.id} className="border-b">
                                    <td className="py-2 font-medium">
                                        {unit.code}
                                    </td>
                                    <td className="py-2">{unit.type_label}</td>
                                    <td className="py-2 text-right">
                                        {unit.size_m2}
                                    </td>
                                    <td className="py-2 text-right">
                                        {euros.format(
                                            unit.monthly_price_cents / 100,
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>
        </>
    );
}
