<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';

/*
 * The design system's empty-state treatment: a brand-tinted icon disc above a
 * heading and a line of body copy, centred on the page canvas.
 *
 * Voice follows docs/design_system/readme.md's content rules — Title Case
 * headings, second-person body copy, no emoji. Deliberately NOT the UI kit's
 * copy, which is car-rental language ("Rent Now", "Popular Cars") left over
 * from the screenshot the design system was derived from.
 */
defineProps<{
    icon: LucideIcon;
    title: string;
    description: string;
}>();
</script>

<template>
    <div
        class="mx-auto flex w-full max-w-6xl flex-1 flex-col items-center justify-center px-5 py-16 text-center"
    >
        <span
            class="bg-brand-subtle text-brand-on-subtle mb-5 flex size-16 items-center justify-center rounded-full"
        >
            <component :is="icon" class="size-7" />
        </span>

        <h2 class="text-h2 mb-2">{{ title }}</h2>

        <p class="text-muted-foreground text-body max-w-[var(--content-max)]">
            {{ description }}
        </p>

        <div class="mt-6">
            <slot />
        </div>
    </div>
</template>
