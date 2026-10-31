import { X } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDate } from '@/lib/format';

type Props = {
    name: string;
    defaultDays?: string[];
    onChange?: (days: string[]) => void;
};

/**
 * Pick specific days one by one. They are sent with the form as `name[]`.
 */
export default function DaysPicker({
    name,
    defaultDays = [],
    onChange,
}: Props) {
    const [days, setDays] = useState<string[]>(defaultDays);
    const [draft, setDraft] = useState('');

    const update = (next: string[]) => {
        setDays(next);
        onChange?.(next);
    };

    const add = () => {
        if (draft && !days.includes(draft)) {
            update([...days, draft].sort());
        }

        setDraft('');
    };

    return (
        <div className="grid gap-3">
            <div className="flex gap-2">
                <Input
                    type="date"
                    value={draft}
                    onChange={(event) => setDraft(event.target.value)}
                    aria-label="Día a reservar"
                />
                <Button
                    type="button"
                    variant="outline"
                    onClick={add}
                    disabled={!draft}
                >
                    Añadir día
                </Button>
            </div>

            {days.length === 0 ? (
                <p className="text-sm text-muted-foreground">
                    Todavía no hay días elegidos.
                </p>
            ) : (
                <ul className="flex flex-wrap gap-2">
                    {days.map((day) => (
                        <li
                            key={day}
                            className="flex items-center gap-1 rounded-md border px-2 py-1 text-sm"
                        >
                            {formatDate(day)}
                            <input
                                type="hidden"
                                name={`${name}[]`}
                                value={day}
                            />
                            <button
                                type="button"
                                onClick={() =>
                                    update(
                                        days.filter((other) => other !== day),
                                    )
                                }
                                aria-label={`Quitar ${formatDate(day)}`}
                                className="text-muted-foreground hover:text-foreground"
                            >
                                <X className="size-3" />
                            </button>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
