export type BookingMode = 'annual' | 'monthly' | 'weekly' | 'days';

/**
 * What one charge of each mode covers, shown next to its price.
 */
export const pricePeriodLabel: Record<BookingMode, string> = {
    annual: '/ año',
    monthly: '/ mes',
    weekly: '/ semana',
    days: 'en total',
};

/**
 * Unit of the "number of periods" field of each recurring mode.
 */
export const periodsLabel: Record<Exclude<BookingMode, 'days'>, string> = {
    annual: 'años',
    monthly: 'meses',
    weekly: 'semanas',
};
