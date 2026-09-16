<script setup lang="ts">
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import ShellCheckerStrip from '@/components/shell/ShellCheckerStrip.vue';
import ShellDrawer from '@/components/shell/ShellDrawer.vue';
import ShellRail from '@/components/shell/ShellRail.vue';
import ShellTopBar from '@/components/shell/ShellTopBar.vue';
import { Toaster } from '@/components/ui/sonner';
import { useQuickAdd } from '@/composables/useQuickAdd';

/*
 * The Ignition Index shell: navy bar, checker strip, navy rail, light stage.
 *
 * The breakpoint switch is CSS-only (Tailwind md:), deliberately — a JS
 * useMediaQuery returns false on the first tick, so a phone would flash the
 * desktop rail before correcting itself.
 *
 * The checker strip is rendered here rather than by any screen, so design doc
 * §1's "exactly once per page" is structural rather than a convention someone
 * has to remember.
 */
const navOpen = ref(false);

const { triggerQuickAdd } = useQuickAdd();

// What "add" means depends on the screen, so the page claims the button. Any
// screen that has not claimed it says so rather than failing silently.
function onAdd(): void {
    if (!triggerQuickAdd()) {
        toast.info('Open a vehicle to log an entry.');
    }
}
</script>

<template>
    <div class="bg-background flex min-h-screen flex-col">
        <ShellTopBar @add="onAdd" @open-nav="navOpen = true" />
        <ShellCheckerStrip />

        <div class="flex flex-1 items-stretch">
            <aside
                class="hidden w-[216px] flex-none md:block"
                :style="{ background: 'var(--shell-navy-deep)' }"
            >
                <ShellRail />
            </aside>

            <main
                class="flex min-w-0 flex-1 flex-col gap-[26px] px-[30px] pt-[26px] pb-10 max-md:px-5"
            >
                <slot />
            </main>
        </div>

        <ShellDrawer v-model:open="navOpen" />
        <Toaster />
    </div>
</template>
