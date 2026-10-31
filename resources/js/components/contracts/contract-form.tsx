import { Form } from '@inertiajs/react';
import { useState } from 'react';
import {
    type BookingMode,
    periodsLabel,
    pricePeriodLabel,
} from '@/components/contracts/booking';
import DaysPicker from '@/components/contracts/days-picker';
import InputError from '@/components/input-error';
import SelectField from '@/components/select-field';
import TextField from '@/components/text-field';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { formatCents } from '@/lib/format';
import type { RouteFormDefinition } from '@/wayfinder';

/**
 * A unit with what each booking mode would cost today (per period, or per day).
 */
export type UnitOption = {
    id: number;
    label: string;
    prices: Record<BookingMode, number>;
};
export type CustomerOption = { id: number; label: string };

export type ContractFormValues = {
    unit_id: string;
    customer_id: string;
    mode: BookingMode;
    starts_on: string | null;
    periods: number | null;
    days: string[];
    price_cents: number;
};

export type ContractFormOptions = {
    units: UnitOption[];
    customers: CustomerOption[];
    modes: { value: BookingMode; label: string }[];
};

type Props = ContractFormOptions & {
    action: RouteFormDefinition<'post'>;
    defaults?: ContractFormValues;
    submitLabel: string;
};

const toOptions = (items: { id: number; label: string }[]) =>
    items.map((item) => ({ value: String(item.id), label: item.label }));

function PricePreview({
    unit,
    mode,
    daysCount,
    agreed,
}: {
    unit: UnitOption | undefined;
    mode: BookingMode;
    daysCount: number;
    agreed?: number;
}) {
    if (!unit) {
        return null;
    }

    const price =
        mode === 'days'
            ? `${daysCount} × ${formatCents(unit.prices.days)} = ${formatCents(daysCount * unit.prices.days)}`
            : formatCents(unit.prices[mode]);

    return (
        <div className="rounded-md border bg-muted/40 p-3 text-sm">
            <p>
                Precio con las condiciones actuales:{' '}
                <strong>
                    {price} {pricePeriodLabel[mode]}
                </strong>
            </p>
            {agreed !== undefined && (
                <p className="mt-1 text-muted-foreground">
                    Precio pactado: {formatCents(agreed)}. Se mantiene salvo que
                    cambies la unidad, la modalidad o el número de días.
                </p>
            )}
        </div>
    );
}

export default function ContractForm({
    action,
    units,
    customers,
    modes,
    defaults,
    submitLabel,
}: Props) {
    const [mode, setMode] = useState<BookingMode>(defaults?.mode ?? 'monthly');
    const [unitId, setUnitId] = useState(defaults?.unit_id ?? '');
    const [daysCount, setDaysCount] = useState(defaults?.days.length ?? 0);
    const unit = units.find((option) => String(option.id) === unitId);

    return (
        <Form {...action} className="space-y-6">
            {({ processing, errors }) => (
                <>
                    <SelectField
                        label="Unidad"
                        name="unit_id"
                        options={toOptions(units)}
                        placeholder="Elige una unidad"
                        defaultValue={defaults?.unit_id}
                        error={errors.unit_id}
                        onValueChange={setUnitId}
                    />
                    <SelectField
                        label="Cliente"
                        name="customer_id"
                        options={toOptions(customers)}
                        placeholder="Elige un cliente"
                        defaultValue={defaults?.customer_id}
                        error={errors.customer_id}
                    />
                    <SelectField
                        label="Modalidad"
                        name="mode"
                        options={modes}
                        placeholder="Elige una modalidad"
                        defaultValue={mode}
                        error={errors.mode}
                        onValueChange={(value) => setMode(value as BookingMode)}
                    />

                    {mode === 'days' ? (
                        <div className="grid gap-2">
                            <Label>Días</Label>
                            <DaysPicker
                                name="days"
                                defaultDays={defaults?.days}
                                onChange={(days) => setDaysCount(days.length)}
                            />
                            <InputError message={errors.days} />
                        </div>
                    ) : (
                        <div className="grid gap-6 sm:grid-cols-2">
                            <TextField
                                label="Inicio"
                                name="starts_on"
                                type="date"
                                defaultValue={defaults?.starts_on ?? ''}
                                error={errors.starts_on}
                                required
                            />
                            <TextField
                                label={`Duración en ${periodsLabel[mode]} (vacío: indefinida)`}
                                name="periods"
                                type="number"
                                min="1"
                                step="1"
                                defaultValue={defaults?.periods ?? ''}
                                error={errors.periods}
                            />
                        </div>
                    )}
                    <InputError message={errors.dates} />

                    <PricePreview
                        unit={unit}
                        mode={mode}
                        daysCount={daysCount}
                        agreed={defaults?.price_cents}
                    />

                    <Button disabled={processing}>{submitLabel}</Button>
                </>
            )}
        </Form>
    );
}
