import { usePage } from '@inertiajs/vue3';
import { ChartColumn, ClipboardList, Warehouse } from '@lucide/vue';
import { computed } from 'vue';
import type { ComputedRef } from 'vue';
import { garage, history, insights } from '@/routes';
import type { NavItem } from '@/types';

/**
 * The rail's three destinations, in one place.
 *
 * The desktop rail and the mobile drawer render the same list, so the two
 * cannot drift apart. Settings is deliberately absent: design doc §5 puts it in
 * the user block in the top bar, not the rail.
 *
 * Garage badges the VEHICLE count, not the due count. Urgency lives in the Due
 * next list directly beneath it and in the bell's rust badge; badging it here
 * too would put two competing urgency signals in one 216px column.
 */
export function useShellNav(): ComputedRef<NavItem[]> {
    const page = usePage();

    return computed(() => [
        {
            title: 'Garage',
            href: garage(),
            icon: Warehouse,
            badge: page.props.garage?.vehicle_count ?? 0,
        },
        { title: 'History', href: history(), icon: ClipboardList },
        { title: 'Insights', href: insights(), icon: ChartColumn },
    ]);
}
