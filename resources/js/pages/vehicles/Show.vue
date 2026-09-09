<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ClipboardList, Pencil, Plus } from '@lucide/vue';
import { ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import EventRow from '@/components/EventRow.vue';
import VehicleColorDot from '@/components/VehicleColorDot.vue';
import QuickAddSheet from '@/components/QuickAddSheet.vue';
import { Button } from '@/components/ui/button';
import { useQuickAdd } from '@/composables/useQuickAdd';
import { formatDate, formatMiles } from '@/lib/format';
import { garage } from '@/routes';
import { edit } from '@/routes/vehicles';
import type {
    ServiceTypeOption,
    VehicleDetail,
    VehicleEvent,
} from '@/types/garage';

const props = defineProps<{
    vehicle: VehicleDetail;
    events: VehicleEvent[];
    serviceTypes: ServiceTypeOption[];
    expenseCategories: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Garage', href: garage() }],
    },
});

const photoFailed = ref(false);
const quickAddOpen = ref(false);
const { claimQuickAdd } = useQuickAdd();

claimQuickAdd(() => {
    quickAddOpen.value = true;
});

const specs = [
    props.vehicle.year,
    props.vehicle.make,
    props.vehicle.model,
    props.vehicle.trim,
]
    .filter(Boolean)
    .join(' ');
</script>

<template>
    <Head :title="vehicle.name" />

    <div
        class="mx-auto flex w-full max-w-6xl flex-col gap-5 px-5 pb-4 md:px-(--card-pad)"
    >
        <div class="bg-card rounded-lg p-(--card-pad) shadow-md">
            <!--
                Above the fold and the page's LCP element, so no lazy loading.
                The wrapper reserves the aspect ratio and carries the dominant
                colour, so nothing shifts and the loading state is a tinted band
                rather than a grey hole.
            -->
            <div
                v-if="vehicle.photo_url && !photoFailed"
                class="bg-muted mb-4 aspect-[16/9] w-full overflow-hidden rounded-lg"
                :style="
                    vehicle.photo_color
                        ? { backgroundColor: vehicle.photo_color }
                        : undefined
                "
            >
                <img
                    :src="vehicle.photo_url"
                    :alt="`Photo of ${vehicle.name}`"
                    fetchpriority="high"
                    decoding="async"
                    class="size-full object-cover"
                    @error="photoFailed = true"
                />
            </div>

            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="text-h1 flex items-center gap-2.5">
                        <VehicleColorDot
                            :color="vehicle.color"
                            :label="`Paint colour ${vehicle.color}`"
                        />
                        <span class="truncate">{{ vehicle.name }}</span>
                    </h1>
                    <p v-if="specs" class="text-muted-foreground text-body">
                        {{ specs }}
                    </p>
                </div>

                <Button
                    as-child
                    variant="surface"
                    size="icon-tap"
                    aria-label="Edit vehicle"
                >
                    <Link :href="edit(vehicle.id)">
                        <Pencil />
                    </Link>
                </Button>
            </div>

            <div class="border-border mt-4 border-t pt-4">
                <p class="ct-score">
                    {{ formatMiles(vehicle.last_odometer) ?? '—' }}
                </p>
                <p
                    v-if="vehicle.last_odometer_at"
                    class="text-muted-foreground text-sm"
                >
                    Last reading {{ formatDate(vehicle.last_odometer_at) }}
                </p>
            </div>

            <p v-if="vehicle.vin" class="text-muted-foreground mt-3 text-xs">
                VIN {{ vehicle.vin }}
            </p>
        </div>

        <div class="flex items-center justify-between">
            <h2 class="text-h2">History</h2>
            <Button size="sm" @click="quickAddOpen = true">
                <Plus />
                Log Entry
            </Button>
        </div>

        <EmptyState
            v-if="events.length === 0"
            :icon="ClipboardList"
            title="Nothing Logged Yet"
            description="Log a fill-up, a service visit or just today’s mileage to start building this vehicle’s record."
        />

        <ul v-else class="flex flex-col gap-3">
            <EventRow v-for="event in events" :key="event.id" :event="event" />
        </ul>
    </div>

    <QuickAddSheet
        v-model:open="quickAddOpen"
        :vehicles="[vehicle]"
        :service-types="serviceTypes"
        :expense-categories="expenseCategories"
    />
</template>
