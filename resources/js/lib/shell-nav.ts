import { usePage } from '@inertiajs/vue3';
import { ChartColumn, ClipboardList, Settings, Warehouse } from '@lucide/vue';
import { computed } from 'vue';
import type { ComputedRef } from 'vue';
import { garage, history, insights } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { NavItem } from '@/types';

/**
 * The app shell's four destinations, in one place.
 *
 * Both the mobile bottom nav and the desktop rail render this, so the two
 * cannot drift apart. Order matters for the bottom nav: the design system
 * splits the items two-and-two around the centre FAB, so this must stay
 * exactly four entries.
 *
 * Garage carries a badge for anything due across the whole garage. Without it a
 * reminder is only visible to someone who already went looking for it.
 */
export function useShellNav(): ComputedRef<NavItem[]> {
    const page = usePage();

    return computed(() => [
        {
            title: 'Garage',
            href: garage(),
            icon: Warehouse,
            badge: page.props.garage?.due_count ?? 0,
        },
        { title: 'History', href: history(), icon: ClipboardList },
        { title: 'Insights', href: insights(), icon: ChartColumn },
        { title: 'Settings', href: editProfile(), icon: Settings },
    ]);
}
