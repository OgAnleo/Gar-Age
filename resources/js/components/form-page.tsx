import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import Heading from '@/components/heading';

/**
 * Layout of a create/edit page: document title, heading and a narrow form.
 */
export default function FormPage({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    return (
        <>
            <Head title={title} />
            <div className="max-w-xl p-4">
                <Heading title={title} />
                {children}
            </div>
        </>
    );
}
