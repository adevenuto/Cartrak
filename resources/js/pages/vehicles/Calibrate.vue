<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import GaugeRing from '@/components/GaugeRing.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { garage } from '@/routes';
import { show } from '@/routes/vehicles';
import { store } from '@/actions/App/Http/Controllers/VehicleIntervalController';
import type { GaugeStatus } from '@/types/gauges';

/*
 * Quick calibrate: the short, skippable step straight after adding a car.
 *
 * The brief calls this the wow — each answer visibly fills a ring, so setup
 * itself demonstrates what the app does. Questions are deliberately coarse
 * ("roughly when?"), because nobody remembers the date of their last oil
 * change and asking for one would stall the flow.
 *
 * The odometer is optional per item but worth asking: without it the mileage
 * axis has no baseline, so a gauge can only count down on time until a service
 * is logged.
 */
type Interval = {
    id: number;
    name: string;
    interval_months: number | null;
    interval_miles: number | null;
    last_done_at: string | null;
    last_done_odometer: number | null;
};

const props = defineProps<{
    vehicle: { id: number; name: string; last_odometer: number | null };
    intervals: Interval[];
    estimates: { value: string; label: string }[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Garage', href: garage() }],
    },
});

const answers = ref<Record<number, { estimate: string; odometer: string }>>({});
const processing = ref(false);

/**
 * A filled ring the moment an answer is given. "Not sure" deliberately leaves
 * the ring empty — an honest unknown, not a fabricated countdown.
 */
function ringFor(interval: Interval): {
    progress: number;
    status: GaugeStatus;
} {
    const answer = answers.value[interval.id];

    if (!answer || answer.estimate === '' || answer.estimate === 'not_sure') {
        return { progress: 0, status: 'uncalibrated' };
    }

    const elapsed: Record<string, number> = {
        this_month: 0.05,
        '1_3': 0.3,
        '3_6': 0.6,
        '6_12': 0.85,
        over_year: 1,
    };

    const progress = elapsed[answer.estimate] ?? 0;

    return {
        progress,
        status:
            progress >= 1 ? 'overdue' : progress >= 0.8 ? 'soon' : 'healthy',
    };
}

const answeredCount = computed(
    () =>
        Object.values(answers.value).filter(
            (a) => a.estimate !== '' && a.estimate !== 'not_sure',
        ).length,
);

function choose(interval: Interval, estimate: string): void {
    answers.value[interval.id] = {
        estimate,
        odometer: answers.value[interval.id]?.odometer ?? '',
    };
}

function submit(): void {
    processing.value = true;

    router.post(
        store.url(props.vehicle.id),
        {
            answers: Object.entries(answers.value)
                .filter(([, a]) => a.estimate !== '')
                .map(([id, a]) => ({
                    interval_id: Number(id),
                    estimate: a.estimate,
                    odometer: a.odometer === '' ? null : Number(a.odometer),
                })),
        },
        { onFinish: () => (processing.value = false) },
    );
}
</script>

<template>
    <Head title="Set up your gauges" />

    <div class="mx-auto w-full max-w-6xl px-5 pb-8 md:max-w-2xl">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-h1">Let's start your gauges</h1>
                <p class="text-muted-foreground text-body">
                    Roughly when were these last done on {{ vehicle.name }}? You
                    can skip anything you're unsure about.
                </p>
            </div>

            <Button
                as-child
                variant="ghost"
                class="text-muted-foreground shrink-0"
            >
                <Link :href="show(vehicle.id)">Skip</Link>
            </Button>
        </div>

        <div class="flex flex-col gap-4">
            <Card v-for="interval in intervals" :key="interval.id">
                <CardContent class="flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <GaugeRing
                            v-bind="ringFor(interval)"
                            :size="56"
                            :thickness="5"
                        />
                        <div class="min-w-0">
                            <p class="text-title">{{ interval.name }}</p>
                            <p class="text-muted-foreground text-xs">
                                <template
                                    v-if="
                                        interval.interval_miles &&
                                        interval.interval_months
                                    "
                                >
                                    Every
                                    {{
                                        interval.interval_miles.toLocaleString()
                                    }}
                                    mi or {{ interval.interval_months }} months
                                </template>
                                <template v-else-if="interval.interval_miles">
                                    Every
                                    {{
                                        interval.interval_miles.toLocaleString()
                                    }}
                                    mi
                                </template>
                                <template v-else-if="interval.interval_months">
                                    Every {{ interval.interval_months }} months
                                </template>
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button
                            v-for="estimate in estimates"
                            :key="estimate.value"
                            type="button"
                            size="sm"
                            :variant="
                                answers[interval.id]?.estimate ===
                                estimate.value
                                    ? 'default'
                                    : 'surface'
                            "
                            @click="choose(interval, estimate.value)"
                        >
                            {{ estimate.label }}
                        </Button>
                    </div>

                    <div
                        v-if="
                            answers[interval.id] &&
                            answers[interval.id].estimate !== 'not_sure' &&
                            interval.interval_miles
                        "
                        class="grid gap-1.5"
                    >
                        <Label :for="`odo-${interval.id}`">
                            Odometer then
                            <span class="text-muted-foreground font-normal">
                                — optional, but it sharpens the mileage
                                countdown
                            </span>
                        </Label>
                        <Input
                            :id="`odo-${interval.id}`"
                            v-model="answers[interval.id].odometer"
                            type="number"
                            inputmode="numeric"
                            :placeholder="
                                vehicle.last_odometer
                                    ? String(vehicle.last_odometer)
                                    : 'Miles at the time'
                            "
                        />
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <p class="text-muted-foreground mr-auto text-sm">
                {{ answeredCount }} of {{ intervals.length }} set
            </p>
            <Button as-child variant="ghost" class="text-muted-foreground">
                <Link :href="show(vehicle.id)">Skip for now</Link>
            </Button>
            <Button type="button" :disabled="processing" @click="submit">
                Save & See Gauges
            </Button>
        </div>
    </div>
</template>
