<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { shellNavItems } from '@/lib/shell-nav';

/*
 * Ported from docs/design_system/components/navigation/BottomNav.jsx.
 *
 * Faithful to the spec: 28px top corners, a soft upward shadow, items split
 * two-and-two around a 78px gap, and a 62px crimson FAB raised 22px above the
 * bar with a 4px ring and the brand glow.
 *
 * Two deliberate departures from the JSX, both required here:
 *
 *   1. Tabs are Inertia <Link>s, not <button @click>s. The design-system kit is a
 *      client-side useState click-through; this app is server-driven, so the
 *      active tab comes from the current URL rather than an `active` prop.
 *   2. Press feedback is CSS :active, not the JSX's onMouseDown handlers — this
 *      is what docs/design_system/guidelines/vue-usage.md's porting checklist
 *      prescribes for Vue.
 *
 * The safe-area inset goes on the BAR's padding, not on the fixed wrapper, so
 * the white surface extends under the iOS home indicator instead of leaving a
 * transparent gap below it. It needs viewport-fit=cover in the viewport meta
 * (set in resources/views/app.blade.php) or env() resolves to 0px.
 */
const emit = defineEmits<{
    add: [];
}>();

const { isCurrentOrParentUrl } = useCurrentUrl();

const half = Math.ceil(shellNavItems.length / 2);
const leftItems = computed(() => shellNavItems.slice(0, half));
const rightItems = computed(() => shellNavItems.slice(half));
</script>

<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-40 md:hidden"
        aria-label="Primary"
        data-test="bottom-nav"
    >
        <div
            class="bg-card relative flex items-start rounded-t-xl px-[22px] pt-3.5 shadow-[0_-8px_24px_rgba(26,26,26,0.06)] dark:shadow-[0_-8px_24px_rgba(0,0,0,0.5)]"
            style="padding-bottom: calc(20px + env(safe-area-inset-bottom))"
        >
            <div class="flex flex-1 gap-1">
                <Link
                    v-for="item in leftItems"
                    :key="item.title"
                    :href="item.href"
                    class="ease-standard flex min-h-11 flex-1 flex-col items-center gap-1 py-1 transition-colors duration-[var(--dur-fast)] active:scale-[var(--press-scale)]"
                    :class="
                        isCurrentOrParentUrl(item.href)
                            ? 'text-brand-on-subtle font-bold'
                            : 'font-medium text-[var(--ink-400)]'
                    "
                    :aria-current="
                        isCurrentOrParentUrl(item.href) ? 'page' : undefined
                    "
                >
                    <component :is="item.icon" class="size-5" />
                    <span class="text-xs leading-none">{{ item.title }}</span>
                </Link>
            </div>

            <!-- Gap the FAB sits in; matches the 78px spacer in BottomNav.jsx. -->
            <div class="w-[78px] shrink-0" aria-hidden="true" />

            <div class="flex flex-1 gap-1">
                <Link
                    v-for="item in rightItems"
                    :key="item.title"
                    :href="item.href"
                    class="ease-standard flex min-h-11 flex-1 flex-col items-center gap-1 py-1 transition-colors duration-[var(--dur-fast)] active:scale-[var(--press-scale)]"
                    :class="
                        isCurrentOrParentUrl(item.href)
                            ? 'text-brand-on-subtle font-bold'
                            : 'font-medium text-[var(--ink-400)]'
                    "
                    :aria-current="
                        isCurrentOrParentUrl(item.href) ? 'page' : undefined
                    "
                >
                    <component :is="item.icon" class="size-5" />
                    <span class="text-xs leading-none">{{ item.title }}</span>
                </Link>
            </div>

            <!--
                The raised centre FAB is the design system's bottom-nav
                silhouette. What it does is decided by the page (see
                composables/useQuickAdd.ts).
            -->
            <Button
                type="button"
                size="icon"
                data-test="bottom-nav-add"
                class="border-card absolute -top-[22px] left-1/2 size-[62px] -translate-x-1/2 border-4"
                @click="emit('add')"
            >
                <span class="sr-only">Add a log entry</span>
                <Plus class="size-7" />
            </Button>
        </div>
    </nav>
</template>
