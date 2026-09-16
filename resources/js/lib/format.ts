/**
 * Display formatting for the values the spine stores.
 *
 * Costs are integer cents in the database and are only ever turned into dollars
 * here, at the edge, so no rounding creeps into the data itself.
 */

const dollars = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
});

const wholeDollars = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 0,
});

export function formatCents(cents: number | null | undefined): string | null {
    if (cents === null || cents === undefined) {
        return null;
    }

    return dollars.format(cents / 100);
}

export function formatCentsShort(
    cents: number | null | undefined,
): string | null {
    if (cents === null || cents === undefined) {
        return null;
    }

    return wholeDollars.format(cents / 100);
}

/**
 * A grouped number with no unit. The vehicle card's metric cells render the
 * unit themselves, at half the size in a lighter ink, so they need the figure
 * on its own — formatMiles already carries " mi" and would double it up.
 */
export function formatNumber(value: number | null | undefined): string | null {
    if (value === null || value === undefined) {
        return null;
    }

    return new Intl.NumberFormat('en-US').format(value);
}

export function formatMiles(miles: number | null | undefined): string | null {
    if (miles === null || miles === undefined) {
        return null;
    }

    return `${new Intl.NumberFormat('en-US').format(miles)} mi`;
}

/**
 * Dates arrive as plain YYYY-MM-DD strings. Parsing them with `new Date()`
 * would treat them as UTC midnight and shift the day backwards for anyone west
 * of Greenwich, so the parts are split by hand.
 */
export function formatDate(date: string | null | undefined): string | null {
    if (!date) {
        return null;
    }

    const [year, month, day] = date.split('-').map(Number);

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(year, month - 1, day));
}

export function todayIso(): string {
    const now = new Date();
    const month = `${now.getMonth() + 1}`.padStart(2, '0');
    const day = `${now.getDate()}`.padStart(2, '0');

    return `${now.getFullYear()}-${month}-${day}`;
}
