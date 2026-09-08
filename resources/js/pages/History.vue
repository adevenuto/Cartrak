<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ClipboardList } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import { history } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'History',
                href: history(),
            },
        ],
    },
});
</script>

<template>
    <Head title="History" />

    <EmptyState
        :icon="ClipboardList"
        title="Nothing Logged Yet"
        description="Every service, fuel-up and expense you record shows up here, building the service record you can hand to a buyer."
    />
</template>
