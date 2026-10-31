const euros = new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'EUR',
});

/**
 * 7999 → "79,99 €".
 */
export function formatCents(cents: number): string {
    return euros.format(cents / 100);
}

const months = new Intl.DateTimeFormat('es-ES', {
    month: 'short',
    timeZone: 'UTC',
});

/**
 * "2026-03" → "mar" (read in UTC, so no timezone shifts).
 */
export function formatMonth(isoMonth: string): string {
    const [year, month] = isoMonth.split('-').map(Number);

    return months.format(new Date(Date.UTC(year, month - 1, 1)));
}

/**
 * "2026-03-01" → "01/03/2026" (string based, so no timezone shifts).
 */
export function formatDate(isoDate: string): string {
    const [year, month, day] = isoDate.split('-');

    return `${day}/${month}/${year}`;
}
