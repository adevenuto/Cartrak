<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import GaugeRing from '@/components/GaugeRing.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { update as updateInterval } from '@/actions/App/Http/Controllers/VehicleIntervalController';
import { todayIso } from '@/lib/format';
import type { Gauge } from '@/types/gauges';

/*
 * Adjust one gauge in place.
 *
 * A modal rather than a route: setting a last-done date is a few seconds of
 * work, and navigating away from the cluster to do it loses the context of the
 * other gauges you were comparing it against.
 *
 * The rough buckets fill the date field rather than replacing it. Nobody
 * remembers the date of their last oil change, but the field stays there for
 * anyone who does — or who wants to correct a bucket afterwards.
 */
const props = defineProps<{
    gauge: Gauge | null;
    projectedOdometer: number;
}>();

const open = defineModel<boolean>('open', { required: true });

const showInterval = ref(false);
const lastDoneAt = ref('');
const lastDoneOdometer = ref('');

const BUCKETS: { label: string; months: number }[] = [
    { label: 'This month', months: 0 },
    { label: '1–3 mo', months: 2 },
    { label: '3–6 mo', months: 4 },
    { label: '6–12 mo', months: 9 },
    { label: 'Over a year', months: 18 },
];

// Reseed from the gauge each time it opens, so reopening never shows the
// previous gauge's answers.
watch(
    () => [open.value, props.gauge?.id],
    () => {
        if (open.value && props.gauge) {
            lastDoneAt.value = props.gauge.last_done_at ?? '';
            lastDoneOdometer.value =
                props.gauge.last_done_odometer === null
                    ? ''
                    : String(props.gauge.last_done_odometer);
            showInterval.value = false;
        }
    },
    { immediate: true },
);

function chooseBucket(months: number): void {
    const date = new Date();
    date.setMonth(date.getMonth() - months);

    const month = `${date.getMonth() + 1}`.padStart(2, '0');
    const day = `${date.getDate()}`.padStart(2, '0');

    lastDoneAt.value = `${date.getFullYear()}-${month}-${day}`;
}

const intervalSummary = computed(() => {
    if (!props.gauge) {
        return '';
    }

    const parts: string[] = [];

    if (props.gauge.interval_miles) {
        parts.push(`${props.gauge.interval_miles.toLocaleString()} mi`);
    }

    if (props.gauge.interval_months) {
        parts.push(`${props.gauge.interval_months} months`);
    }

    return parts.length ? `Every ${parts.join(' or ')}` : 'No interval set';
});
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent v-if="gauge" class="sm:max-w-md">
            <DialogHeader class="text-left">
                <div class="flex items-center gap-3">
                    <GaugeRing
                        :progress="gauge.progress"
                        :status="gauge.status"
                        :size="48"
                        :thickness="5"
                    />
                    <div class="min-w-0">
                        <DialogTitle class="text-h3">
                            {{ gauge.name }}
                        </DialogTitle>
                        <DialogDescription>
                            {{ intervalSummary }}
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <Form
                v-bind="updateInterval.form(gauge.id)"
                :options="{ preserveScroll: true }"
                class="flex flex-col gap-5"
                v-slot="{ errors, processing }"
                @success="open = false"
            >
                <div class="grid gap-2">
                    <Label>When was this last done?</Label>

                    <div class="flex flex-wrap gap-2">
                        <Button
                            v-for="bucket in BUCKETS"
                            :key="bucket.label"
                            type="button"
                            size="sm"
                            variant="surface"
                            @click="chooseBucket(bucket.months)"
                        >
                            {{ bucket.label }}
                        </Button>
                    </div>

                    <Input
                        id="last_done_at"
                        v-model="lastDoneAt"
                        name="last_done_at"
                        type="date"
                        :max="todayIso()"
                    />
                    <InputError :message="errors.last_done_at" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="last_done_odometer">
                        Odometer then
                        <span class="text-muted-foreground font-normal">
                            — optional
                        </span>
                    </Label>
                    <Input
                        id="last_done_odometer"
                        v-model="lastDoneOdometer"
                        name="last_done_odometer"
                        type="number"
                        inputmode="numeric"
                        :placeholder="String(projectedOdometer)"
                    />
                    <p class="text-muted-foreground text-xs">
                        Without this, only the time side of the gauge can count
                        down.
                    </p>
                    <InputError :message="errors.last_done_odometer" />
                </div>

                <Button
                    v-if="!showInterval"
                    type="button"
                    variant="ghost"
                    size="sm"
                    class="self-start"
                    @click="showInterval = true"
                >
                    Adjust how often
                </Button>

                <div v-else class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="interval_miles">Every (miles)</Label>
                        <Input
                            id="interval_miles"
                            name="interval_miles"
                            type="number"
                            inputmode="numeric"
                            :default-value="gauge.interval_miles ?? undefined"
                            placeholder="—"
                        />
                        <InputError :message="errors.interval_miles" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="interval_months">Every (months)</Label>
                        <Input
                            id="interval_months"
                            name="interval_months"
                            type="number"
                            inputmode="numeric"
                            :default-value="gauge.interval_months ?? undefined"
                            placeholder="—"
                        />
                        <InputError :message="errors.interval_months" />
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Button
                        type="button"
                        variant="ghost"
                        class="text-muted-foreground hover:text-foreground"
                        @click="open = false"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">Save</Button>
                </div>
            </Form>
        </DialogContent>
    </Dialog>
</template>
