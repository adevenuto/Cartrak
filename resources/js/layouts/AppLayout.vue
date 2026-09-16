<script setup lang="ts">
import AppShell from '@/components/shell/AppShell.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import type { BreadcrumbItem } from '@/types';

/*
 * Kept at this path, with this name and prop signature, so app.ts's layout
 * switch and all 19 pages are untouched by the shell replacement.
 */
const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <AppShell>
        <Breadcrumbs v-if="breadcrumbs.length" :breadcrumbs="breadcrumbs" />
        <slot />
    </AppShell>
</template>
