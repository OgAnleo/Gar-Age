import { Head, Link } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';

type Props = {
    title: string;
    createHref: NonNullable<InertiaLinkProps['href']>;
    createLabel: string;
    emptyText: string;
    isEmpty: boolean;
    children: ReactNode;
};

/**
 * Layout of a list page: title, "new" button, and the list or an empty message.
 */
export default function ResourceIndex({
    title,
    createHref,
    createLabel,
    emptyText,
    isEmpty,
    children,
}: Props) {
    return (
        <>
            <Head title={title} />
            <div className="flex flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">{title}</h1>
                    <Button asChild>
                        <Link href={createHref}>{createLabel}</Link>
                    </Button>
                </div>

                {isEmpty ? (
                    <p className="text-sm text-muted-foreground">{emptyText}</p>
                ) : (
                    children
                )}
            </div>
        </>
    );
}
