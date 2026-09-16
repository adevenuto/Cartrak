import type { GaugeStatus } from '@/types/gauges';

/**
 * Mirrors config('vehicles.gauges.soon').
 *
 * Only the calibrate screen needs this: it previews a status for an answer the
 * user has not saved yet, so there is no server-computed status to read. Every
 * other gauge takes its status from the backend. Guarded by
 * tests/Unit/DesignTokenSyncTest.php so the two cannot drift.
 */
export const SOON_THRESHOLD = 0.8;

/*
 * How a gauge's state is drawn.
 *
 * The artboard derives colour from the percentage, re-deriving the 75% and 100%
 * thresholds in the view. We derive it from `status` instead, which the backend
 * already computes from config('vehicles.thresholds') — so the threshold lives
 * in exactly one place and the interface cannot disagree with the reminder that
 * was sent about the same interval.
 *
 * Design doc §6 draws three states. The backend keeps five, because Due and
 * Overdue drive different reminder cadences; they collapse here, at the
 * presentation boundary, which is the only place the distinction does not
 * matter.
 */

/** §2: the bright step — fills, arcs and numerals at 20px and above. */
export function gaugeColor(status: GaugeStatus): string {
    switch (status) {
        case 'overdue':
        case 'due':
            return 'var(--status-overdue)';
        case 'soon':
            return 'var(--status-due)';
        case 'healthy':
            return 'var(--color-accent)';
        default:
            // Not a fourth status colour — the absence of one.
            return 'var(--color-neutral-500)';
    }
}

/** §2: the deep step — anything at text size, where the bright step fails AA. */
export function gaugeInk(status: GaugeStatus): string {
    switch (status) {
        case 'overdue':
        case 'due':
            return 'var(--status-overdue-ink)';
        case 'soon':
            return 'var(--status-due-ink)';
        case 'healthy':
            return 'var(--status-ok-ink)';
        default:
            return 'var(--color-neutral-700)';
    }
}

/** §9's vocabulary. "Not set" is unchanged from the existing label(). */
export function gaugeStatusLabel(status: GaugeStatus): string {
    switch (status) {
        case 'overdue':
        case 'due':
            return 'Overdue';
        case 'soon':
            return 'Due soon';
        case 'healthy':
            return 'On interval';
        default:
            return 'Not set';
    }
}

/**
 * The readout. An uncalibrated gauge shows an em dash, never 0% — 0% means
 * "just serviced", which is the opposite of "we have no idea".
 */
export function gaugeReadout(status: GaugeStatus, percent: number): string {
    return status === 'uncalibrated' ? '—' : `${Math.round(percent)}%`;
}
