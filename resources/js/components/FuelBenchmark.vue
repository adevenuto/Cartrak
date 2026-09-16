<script setup lang="ts">
import { Fuel } from '@lucide/vue';
import { computed } from 'vue';
import type { FuelBenchmark } from '@/types/gauges';

/*
 * Real measured MPG against the EPA sticker, as one quiet line.
 *
 * The brief frames this as a diagnostic angle rather than a scoreboard: economy
 * drifting well below the published figure is often the first sign of tyre
 * pressure, a clogged filter or a failing sensor. So a low reading still says
 * what it might mean; everything else stays out of the way.
 *
 * It sits in the footer of the vehicle card because it is genuinely secondary —
 * useful when you look for it, never competing with the gauges.
 */
const props = defineProps<{
    fuel: FuelBenchmark;
}>();

const verdict = computed(() => {
    const delta = props.fuel.delta_percent;

    if (delta === null) {
        return null;
    }

    if (delta >= 5) {
        return { low: false, text: `${delta}% better than EPA` };
    }

    if (delta > -10) {
        return { low: false, text: 'about the EPA rating' };
    }

    return { low: true, text: `${Math.abs(delta)}% below EPA` };
});
</script>

<template>
    <p
        v-if="fuel.actual !== null || fuel.sticker !== null"
        class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-[11px] text-(--color-neutral-700)"
    >
        <Fuel class="size-3.5 flex-none" :stroke-width="1.5" />

        <template v-if="fuel.actual !== null">
            <span class="font-medium text-(--color-text)">
                {{ fuel.actual }} MPG
            </span>
            <span>&middot; last {{ fuel.reading_count }} fill-ups</span>
        </template>

        <template v-if="verdict">
            <span>&middot;</span>
            <span :class="verdict.low ? 'text-(--status-due-ink)' : ''">
                {{ verdict.text
                }}<template v-if="verdict.low">
                    — check tyre pressures and the air filter</template
                >
            </span>
        </template>

        <template v-else-if="fuel.actual === null && fuel.sticker !== null">
            <span>
                EPA {{ fuel.sticker }} MPG &middot; log a few full tanks to
                compare
            </span>
        </template>
    </p>
</template>
