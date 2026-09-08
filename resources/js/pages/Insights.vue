<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ChartColumn } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import { insights } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Insights',
                href: insights(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Insights" />

    <EmptyState
        :icon="ChartColumn"
        title="No Numbers Yet"
        description="Once you have logged a few fill-ups and services, this is where your cost per mile, fuel economy and upcoming spend appear."
    />
</template>
