<script setup lang="ts">
import { Head, Link, router, setLayoutProps } from '@inertiajs/vue3';
import { Pencil } from '@lucide/vue';
import { ref } from 'vue';
import GaugeGallery from '@/components/vehicle/GaugeGallery.vue';
import VehicleHeaderCard from '@/components/vehicle/VehicleHeaderCard.vue';
import VehicleSwitcher from '@/components/vehicle/VehicleSwitcher.vue';
import { BlueprintFrame } from '@/components/ui/blueprint';
import FuelBenchmarkCard from '@/components/FuelBenchmark.vue';
import RecallAlert from '@/components/RecallAlert.vue';
import GaugeEditDialog from '@/components/GaugeEditDialog.vue';
import GaugeDial from '@/components/GaugeDial.vue';
import QuickAddSheet from '@/components/QuickAddSheet.vue';
import { Button } from '@/components/ui/button';
import { useQuickAdd } from '@/composables/useQuickAdd';
import { garage } from '@/routes';
import { calibrate, edit, show } from '@/routes/vehicles';
import type {
    FuelBenchmark,
    Gauge,
    MileageSummary,
    VehicleRecall,
} from '@/types/gauges';
import type {
    ServiceTypeOption,
    VehicleChip,
    VehicleDetail,
} from '@/types/garage';

const props = defineProps<{
    vehicles: VehicleChip[];
    vehicle: VehicleDetail;
    gauges: Gauge[];
    recalls: VehicleRecall[];
    fuel: FuelBenchmark | null;
    mileage: MileageSummary;
    serviceTypes: ServiceTypeOption[];
    expenseCategories: string[];
}>();

/*
 * Breadcrumbs are set here rather than in defineOptions because defineOptions is
 * compiled at build time and cannot see setup bindings — so it can name the
 * parent but never this vehicle, which left "Garage" rendering as the current
 * page: dead text with no way back.
 *
 * setLayoutProps is Inertia's dynamic equivalent. Its store is reset in
 * swapComponent on any navigation that does not preserve state, so this does
 * not leak onto the next page.
 */
setLayoutProps({
    breadcrumbs: [
        { title: 'Garage', href: garage() },
        { title: props.vehicle.name, href: show(props.vehicle.id) },
    ],
});

/*
 * Reminder emails deep-link here as ?log=visit, so the loop is
 * "Oil due -> tap -> Save" rather than landing someone on a page where they
 * still have to find the right button.
 */
const deepLinkLane = new URLSearchParams(window.location.search).get('log');
const validLanes = ['fuel', 'visit', 'expense', 'odometer'] as const;
type Lane = (typeof validLanes)[number];

const initialLane = validLanes.includes(deepLinkLane as Lane)
    ? (deepLinkLane as Lane)
    : undefined;

const quickAddOpen = ref(initialLane !== undefined);
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

    <div class="flex w-full flex-col gap-(--space-6)">
        <VehicleSwitcher :vehicles="vehicles" />

        <VehicleHeaderCard
            :vehicle="vehicle"
            :mileage="mileage"
            @intervals="router.visit(calibrate(vehicle.id))"
        />

        <!-- An open safety recall outranks everything else on the page. -->
        <RecallAlert v-if="recalls.length" :recalls="recalls" />

        <FuelBenchmarkCard v-if="fuel" :fuel="fuel" />

        <!--
            A vehicle added before schedules existed has no intervals at all.
            Without this the section renders as a bare heading with no way
            forward, which is worse than saying so.
        -->
        <BlueprintFrame
            v-if="!gauges.length"
            class="flex flex-col items-center gap-3 p-(--space-6) text-center"
        >
            <GaugeDial
                class="h-16 w-[84px]"
                :percent="0"
                status="uncalibrated"
            />
            <div>
                <p class="font-display text-title">No gauges yet</p>
                <p class="text-[13px] text-(--color-neutral-700)">
                    Tell us roughly when things were last done and they'll start
                    counting down.
                </p>
            </div>
            <Button as-child size="sm">
                <Link :href="calibrate(vehicle.id)"> Set up gauges </Link>
            </Button>
        </BlueprintFrame>

        <GaugeGallery v-else :gauges="gauges" @select="editGauge" />
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
        :initial-lane="initialLane"
    />
</template>
