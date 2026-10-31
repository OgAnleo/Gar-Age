const euros = new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'EUR',
});

const monthAndYear = new Intl.DateTimeFormat('es-ES', {
    month: 'long',
    year: 'numeric',
    timeZone: 'UTC',
});

/**
 * 7999 → "79,99 €".
 */
export function formatCents(cents: number): string {
    return euros.format(cents / 100);
}

/**
 * "2026-03-01" → "01/03/2026" (string based, so no timezone shifts).
 */
export function formatDate(isoDate: string): string {
    const [year, month, day] = isoDate.split('-');

    return `${day}/${month}/${year}`;
}

/**
 * "2026-10" → "octubre de 2026".
 */
export function formatMonth(yearMonth: string): string {
    const [year, month] = yearMonth.split('-').map(Number);

    return monthAndYear.format(new Date(Date.UTC(year, month - 1, 1)));
}
