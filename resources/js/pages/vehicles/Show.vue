<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ClipboardList, Pencil, Plus } from '@lucide/vue';
import { ref } from 'vue';
import EmptyState from '@/components/EmptyState.vue';
import EventRow from '@/components/EventRow.vue';
import GaugeCluster from '@/components/GaugeCluster.vue';
import GaugeEditDialog from '@/components/GaugeEditDialog.vue';
import GaugeRing from '@/components/GaugeRing.vue';
import { Card, CardContent } from '@/components/ui/card';
import VehicleColorDot from '@/components/VehicleColorDot.vue';
import QuickAddSheet from '@/components/QuickAddSheet.vue';
import { Button } from '@/components/ui/button';
import { useQuickAdd } from '@/composables/useQuickAdd';
import { formatDate, formatMiles } from '@/lib/format';
import { garage } from '@/routes';
import { calibrate, edit } from '@/routes/vehicles';
import type { Gauge, MileageSummary } from '@/types/gauges';
import type {
    ServiceTypeOption,
    VehicleDetail,
    VehicleEvent,
} from '@/types/garage';

const props = defineProps<{
    vehicle: VehicleDetail;
    events: VehicleEvent[];
    gauges: Gauge[];
    mileage: MileageSummary;
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
const gaugeOpen = ref(false);
const selectedGauge = ref<Gauge | null>(null);

function editGauge(gauge: Gauge): void {
    selectedGauge.value = gauge;
    gaugeOpen.value = true;
}
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
        class="mx-auto flex w-full max-w-[var(--content-max)] flex-col gap-5 px-5 pb-4 md:max-w-2xl md:px-(--card-pad)"
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
                    {{ formatMiles(mileage.projected_odometer) ?? '—' }}
                </p>
                <p class="text-muted-foreground text-sm">
                    <template v-if="mileage.is_projected">
                        Estimated &middot; last read
                        {{ formatDate(mileage.last_odometer_at) }}
                        <template v-if="mileage.miles_per_day">
                            &middot; about
                            {{ Math.round(mileage.miles_per_day) }} mi/day
                        </template>
                    </template>
                    <template v-else-if="mileage.last_odometer_at">
                        Last reading {{ formatDate(mileage.last_odometer_at) }}
                    </template>
                </p>

                <!--
                    The projection has run far enough past the last real reading that we
                    should confirm rather than keep extrapolating. Threshold is projected
                    MILES, not elapsed days, so a heavy driver is asked sooner than
                    someone doing twenty miles a week.
                -->
                <div
                    v-if="mileage.needs_reading"
                    class="bg-brand-subtle mt-3 flex flex-wrap items-center gap-3 rounded-md p-3"
                >
                    <p class="text-brand-on-subtle min-w-0 flex-1 text-sm">
                        It's been a while — confirm your odometer to keep the
                        gauges accurate.
                    </p>
                    <Button size="sm" @click="quickAddOpen = true">
                        Update mileage
                    </Button>
                </div>
            </div>

            <p v-if="vehicle.vin" class="text-muted-foreground mt-3 text-xs">
                VIN {{ vehicle.vin }}
            </p>
        </div>

        <section class="flex flex-col gap-3">
            <h2 class="text-h2">Gauges</h2>

            <!--
                A vehicle added before schedules existed has no intervals at
                all. Without this the section renders as a bare heading with no
                way forward, which is worse than saying so.
            -->
            <Card v-if="!gauges.length">
                <CardContent
                    class="flex flex-col items-center gap-3 py-6 text-center"
                >
                    <GaugeRing :progress="0" status="uncalibrated" :size="64" />
                    <div>
                        <p class="text-title">No gauges yet</p>
                        <p class="text-muted-foreground text-sm">
                            Tell us roughly when things were last done and
                            they'll start counting down.
                        </p>
                    </div>
                    <Button as-child size="sm">
                        <Link :href="calibrate(vehicle.id)">
                            Set up gauges
                        </Link>
                    </Button>
                </CardContent>
            </Card>

            <GaugeCluster v-else :gauges="gauges" @select="editGauge" />
        </section>

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

    <GaugeEditDialog
        v-model:open="gaugeOpen"
        :gauge="selectedGauge"
        :projected-odometer="mileage.projected_odometer"
    />

    <QuickAddSheet
        v-model:open="quickAddOpen"
        :vehicles="[vehicle]"
        :service-types="serviceTypes"
        :expense-categories="expenseCategories"
    />
</template>
