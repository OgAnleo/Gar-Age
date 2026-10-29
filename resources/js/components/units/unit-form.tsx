import { Form } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { RouteFormDefinition } from '@/wayfinder';

export type UnitTypeOption = { value: string; label: string };

export type UnitFormValues = {
    code: string;
    type: string;
    size_m2: string;
    monthly_price: string;
};

type Props = {
    action: RouteFormDefinition<'post'>;
    types: UnitTypeOption[];
    defaults?: UnitFormValues;
    submitLabel: string;
};

export default function UnitForm({
    action,
    types,
    defaults,
    submitLabel,
}: Props) {
    return (
        <Form {...action} className="space-y-6">
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-2">
                        <Label htmlFor="code">Código</Label>
                        <Input
                            id="code"
                            name="code"
                            defaultValue={defaults?.code}
                            required
                            maxLength={20}
                            placeholder="G-01"
                        />
                        <InputError message={errors.code} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="type">Tipo</Label>
                        <Select name="type" defaultValue={defaults?.type}>
                            <SelectTrigger id="type" className="w-full">
                                <SelectValue placeholder="Elige un tipo" />
                            </SelectTrigger>
                            <SelectContent>
                                {types.map((type) => (
                                    <SelectItem
                                        key={type.value}
                                        value={type.value}
                                    >
                                        {type.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={errors.type} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="size_m2">Superficie (m²)</Label>
                        <Input
                            id="size_m2"
                            name="size_m2"
                            type="number"
                            step="0.01"
                            min="0.5"
                            defaultValue={defaults?.size_m2}
                            required
                        />
                        <InputError message={errors.size_m2} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="monthly_price">
                            Precio mensual (€)
                        </Label>
                        <Input
                            id="monthly_price"
                            name="monthly_price"
                            type="number"
                            step="0.01"
                            min="0"
                            defaultValue={defaults?.monthly_price}
                            required
                        />
                        <InputError message={errors.monthly_price} />
                    </div>

                    <Button disabled={processing}>{submitLabel}</Button>
                </>
            )}
        </Form>
    );
}
