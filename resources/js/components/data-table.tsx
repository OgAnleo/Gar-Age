import type { ReactNode } from 'react';

/**
 * Table of a list page: scrolls sideways instead of squeezing its columns
 * when the screen is narrow.
 */
export default function DataTable({ children }: { children: ReactNode }) {
    return (
        <div className="overflow-x-auto">
            <table className="w-full text-left text-sm whitespace-nowrap [&_td]:pr-4 [&_th]:pr-4">
                {children}
            </table>
        </div>
    );
}
