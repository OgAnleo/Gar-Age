import type { ReactNode } from 'react';

/**
 * Table of a list page: scrolls sideways instead of squeezing its columns
 * when the screen is narrow.
 */
export default function DataTable({ children }: { children: ReactNode }) {
    return (
        <div className="overflow-x-auto rounded-lg border bg-card px-3">
            <table className="w-full text-left text-sm whitespace-nowrap [&_tbody_tr]:transition-colors [&_tbody_tr:hover]:bg-muted/60 [&_tbody_tr:last-child]:border-0 [&_td]:pr-4 [&_th]:pr-4 [&_th]:text-xs [&_th]:font-medium [&_th]:tracking-wide [&_th]:uppercase">
                {children}
            </table>
        </div>
    );
}
