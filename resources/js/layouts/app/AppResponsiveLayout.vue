<script setup lang="ts">
import { toast } from 'vue-sonner';
import AppBottomNav from '@/components/AppBottomNav.vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppTopBar from '@/components/AppTopBar.vue';
import { Toaster } from '@/components/ui/sonner';
import { useQuickAdd } from '@/composables/useQuickAdd';
import type { BreadcrumbItem } from '@/types';

/*
 * The app shell: bottom nav + centre FAB below md, the design system's desktop
 * rail at md and above.
 *
 * The breakpoint switch is CSS-only (Tailwind md:), deliberately — a JS
 * useMediaQuery returns false on the first tick, so a phone would flash the
 * desktop rail before correcting itself, and it would break under SSR.
 *
 * AppBottomNav is a SIBLING of AppContent rather than a child: AppContent
 * renders SidebarInset (<main>), which is the scroll container, and a fixed
 * element inside it would scroll with the content.
 */
type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const { triggerQuickAdd } = useQuickAdd();

// What "add" means depends on the screen, so the page claims the button. Any
// screen that has not claimed it says so rather than failing silently.
function onAdd() {
    if (!triggerQuickAdd()) {
        toast.info('Open a vehicle to log an entry.');
    }
}
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar @add="onAdd" />

        <AppContent variant="sidebar" class="min-w-0 overflow-x-clip">
            <AppTopBar :breadcrumbs="breadcrumbs" />
            <slot />

            <!-- Clears the fixed bar plus the iOS home indicator. -->
            <div
                class="shrink-0 md:hidden"
                style="height: calc(78px + env(safe-area-inset-bottom))"
                aria-hidden="true"
            />
        </AppContent>

        <AppBottomNav @add="onAdd" />
        <Toaster />
    </AppShell>
</template>
