<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import AppWordmark from '@/components/AppWordmark.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Button } from '@/components/ui/button';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import { shellNavItems } from '@/lib/shell-nav';
import { garage } from '@/routes';

/*
 * The desktop rail, after docs/design_system/ui_kits/cartrak-desktop/Sidebar.jsx:
 * 248px, white, brand-tinted active row, pinned crimson CTA at the bottom.
 *
 * variant="sidebar" rather than "inset": the design system's rail sits flush
 * against a hairline border, and "inset" wraps the content area in rounded,
 * shadowed, margined chrome the design system does not have.
 *
 * Below md this is hidden — AppResponsiveLayout renders the bottom nav instead,
 * and with no SidebarTrigger on mobile nothing opens the underlying Sheet.
 */
const emit = defineEmits<{
    add: [];
}>();
</script>

<template>
    <Sidebar variant="sidebar" class="hidden md:flex">
        <SidebarHeader class="px-(--card-pad) py-6">
            <Link :href="garage()" class="flex items-center">
                <AppWordmark />
            </Link>
        </SidebarHeader>

        <SidebarContent class="px-3">
            <NavMain :items="shellNavItems" />
        </SidebarContent>

        <SidebarFooter class="gap-4 px-3 pb-5">
            <!--
                Disabled in Phase 0: vehicle logging arrives with the quick-add
                sheet in Phase 1. Rendered rather than hidden so the rail matches
                the design system, and honest about why it does nothing.
            -->
            <Button
                class="w-full"
                aria-disabled="true"
                data-test="rail-add-vehicle"
                @click="emit('add')"
            >
                <Plus />
                Add Vehicle
            </Button>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
