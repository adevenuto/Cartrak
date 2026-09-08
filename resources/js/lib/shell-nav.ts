import { ChartColumn, ClipboardList, Settings, Warehouse } from '@lucide/vue';
import { garage, history, insights } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { NavItem } from '@/types';

/**
 * The app shell's four destinations, in one place.
 *
 * Both the mobile bottom nav and the desktop rail render this array, so the two
 * cannot drift apart. Order matters for the bottom nav: the design system splits
 * the items two-and-two around the centre FAB (see AppBottomNav.vue), so this
 * must stay exactly four entries.
 */
export const shellNavItems: NavItem[] = [
    {
        title: 'Garage',
        href: garage(),
        icon: Warehouse,
    },
    {
        title: 'History',
        href: history(),
        icon: ClipboardList,
    },
    {
        title: 'Insights',
        href: insights(),
        icon: ChartColumn,
    },
    {
        title: 'Settings',
        href: editProfile(),
        icon: Settings,
    },
];
