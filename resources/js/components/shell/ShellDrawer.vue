<script setup lang="ts">
import ShellRail from '@/components/shell/ShellRail.vue';
import { Sheet, SheetContent, SheetTitle } from '@/components/ui/sheet';

/*
 * The rail below md.
 *
 * A Sheet rather than the shadcn Sidebar's own mobile branch: Sheet is reka's
 * Dialog, so focus trapping, scroll locking and Escape come for free, and it
 * renders the SAME ShellRail body as the desktop column.
 */
const open = defineModel<boolean>('open', { required: true });
</script>

<template>
    <Sheet v-model:open="open">
        <SheetContent
            side="left"
            class="w-[264px] border-0 p-0"
            :style="{ background: 'var(--shell-navy-deep)' }"
        >
            <SheetTitle class="sr-only">Navigation</SheetTitle>
            <ShellRail />
        </SheetContent>
    </Sheet>
</template>
