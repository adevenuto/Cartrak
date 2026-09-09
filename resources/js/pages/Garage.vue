<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Warehouse } from '@lucide/vue';
import { ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import QuickAddSheet from '@/components/QuickAddSheet.vue';
import VehicleCard from '@/components/VehicleCard.vue';
import { Button } from '@/components/ui/button';
import { useQuickAdd } from '@/composables/useQuickAdd';
import { garage } from '@/routes';
import { create } from '@/routes/vehicles';
import type { GarageVehicle, ServiceTypeOption } from '@/types/garage';

const props = defineProps<{
    vehicles: GarageVehicle[];
    serviceTypes: ServiceTypeOption[];
    expenseCategories: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Garage', href: garage() }],
    },
});

const quickAddOpen = ref(false);
const { claimQuickAdd } = useQuickAdd();

// The shell's FAB routes to whatever makes sense here: with no vehicles there
// is nothing to log against yet, so it becomes "add your first car".
function onQuickAdd() {
    if (props.vehicles.length === 0) {
        router.visit(create());

        return;
    }

    quickAddOpen.value = true;
}

claimQuickAdd(onQuickAdd);
</script>

<template>
    <Head title="Garage" />

    <EmptyState
        v-if="vehicles.length === 0"
        :icon="Warehouse"
        title="Your Garage Is Empty"
        description="Add your first vehicle and CarTrak starts tracking what it needs next — by both mileage and time, whichever comes first."
    >
        <Button as-child size="lg">
            <Link :href="create()">
                <Plus />
                Add a Vehicle
            </Link>
        </Button>
    </EmptyState>

    <div
        v-else
        class="mx-auto flex w-full max-w-6xl flex-col gap-4 px-5 pb-4 md:px-(--card-pad)"
    >
        <div class="flex items-center justify-between">
            <p class="text-muted-foreground text-sm">
                {{ vehicles.length }}
                {{ vehicles.length === 1 ? 'vehicle' : 'vehicles' }}
            </p>

            <Button as-child variant="outline" size="sm">
                <Link :href="create()">
                    <Plus />
                    Add Vehicle
                </Link>
            </Button>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <VehicleCard
                v-for="vehicle in vehicles"
                :key="vehicle.id"
                :vehicle="vehicle"
            />
        </div>
    </div>

    <QuickAddSheet
        v-if="vehicles.length > 0"
        v-model:open="quickAddOpen"
        :vehicles="vehicles"
        :service-types="serviceTypes"
        :expense-categories="expenseCategories"
    />
</template>
