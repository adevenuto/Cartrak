<script setup lang="ts">
import { Car } from '@lucide/vue';

/*
 * Brand lockup, after ui_kits/cartrak-desktop/Sidebar.jsx: a crimson tile
 * carrying a Car glyph, followed by the name set in type.
 *
 * The design system ships NO logo and its readme is explicit that one should
 * not be fabricated — so the name is typography, not a mark.
 */
withDefaults(
    defineProps<{
        showName?: boolean;
    }>(),
    {
        showName: true,
    },
);
</script>

<template>
    <span class="flex items-center gap-2.5">
        <span
            class="bg-primary text-primary-foreground flex size-[34px] shrink-0 items-center justify-center rounded-xs"
        >
            <Car class="size-5" />
        </span>
        <span
            v-if="showName"
            class="text-foreground text-[20px] leading-none font-extrabold tracking-[var(--ls-tight)]"
        >
            CarTrak
        </span>
    </span>
</template>
