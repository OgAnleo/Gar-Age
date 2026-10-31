import type { ComponentProps } from 'react';
import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = ComponentProps<typeof Input> & {
    label: string;
    name: string;
    error?: string;
};

/**
 * Labelled input with its validation error underneath.
 */
export default function TextField({
    label,
    name,
    error,
    id = name,
    ...inputProps
}: Props) {
    return (
        <div className="grid gap-2">
            <Label htmlFor={id}>{label}</Label>
            <Input id={id} name={name} {...inputProps} />
            <InputError message={error} />
        </div>
    );
}
