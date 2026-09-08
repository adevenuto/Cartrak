<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Warehouse } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import { garage } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Garage',
                href: garage(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Garage" />

    <EmptyState
        :icon="Warehouse"
        title="Your Garage Is Empty"
        description="Add your first vehicle and CarTrak starts tracking what it needs next — by both mileage and time, whichever comes first."
    />
</template>
