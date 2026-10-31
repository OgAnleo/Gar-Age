import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

export type SelectOption = { value: string; label: string };

type Props = {
    label: string;
    name: string;
    options: SelectOption[];
    placeholder: string;
    defaultValue?: string;
    error?: string;
    onValueChange?: (value: string) => void;
};

/**
 * Labelled select (submitted with the form under `name`) with its validation error.
 */
export default function SelectField({
    label,
    name,
    options,
    placeholder,
    defaultValue,
    error,
    onValueChange,
}: Props) {
    return (
        <div className="grid gap-2">
            <Label htmlFor={name}>{label}</Label>
            <Select
                name={name}
                defaultValue={defaultValue}
                onValueChange={onValueChange}
            >
                <SelectTrigger id={name} className="w-full">
                    <SelectValue placeholder={placeholder} />
                </SelectTrigger>
                <SelectContent>
                    {options.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            <InputError message={error} />
        </div>
    );
}
