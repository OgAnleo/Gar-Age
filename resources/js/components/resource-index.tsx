import { Head, Link } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';

type Props = {
    title: string;
    emptyText: string;
    isEmpty: boolean;
    children: ReactNode;
} & (
    | {
          createHref: NonNullable<InertiaLinkProps['href']>;
          createLabel: string;
      }
    | { actions: ReactNode }
);

/**
 * Layout of a list page: title, a "new" button (or custom actions), and the
 * list or an empty message.
 */
export default function ResourceIndex(props: Props) {
    const { title, emptyText, isEmpty, children } = props;

    return (
        <>
            <Head title={title} />
            <div className="flex flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">{title}</h1>
                    {'actions' in props ? (
                        props.actions
                    ) : (
                        <Button asChild>
                            <Link href={props.createHref}>
                                {props.createLabel}
                            </Link>
                        </Button>
                    )}
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
