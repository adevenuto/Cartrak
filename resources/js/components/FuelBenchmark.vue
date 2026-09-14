<script setup lang="ts">
import { Fuel } from '@lucide/vue';
import { computed } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import type { FuelBenchmark } from '@/types/gauges';

/*
 * Real measured MPG against the EPA sticker.
 *
 * The brief frames this as a diagnostic angle, not a scoreboard: economy
 * drifting well below the published figure is often the first sign of tyre
 * pressure, a clogged filter or a failing sensor. So the copy explains what a
 * gap might mean rather than just scoring the driver.
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
        return { tone: 'good', text: `${delta}% better than the EPA rating` };
    }

    if (delta > -10) {
        return { tone: 'normal', text: 'About the EPA rating' };
    }

    return {
        tone: 'low',
        text: `${Math.abs(delta)}% below the EPA rating`,
    };
});
</script>

<template>
    <Card v-if="fuel.actual !== null || fuel.sticker !== null" class="border-0">
        <CardContent class="flex flex-col gap-3">
            <div class="flex items-center gap-2.5">
                <Fuel class="text-muted-foreground size-5 shrink-0" />
                <h2 class="text-h3">Fuel economy</h2>
            </div>

            <div class="flex flex-wrap items-end gap-x-8 gap-y-3">
                <div v-if="fuel.actual !== null">
                    <p class="ct-score">{{ fuel.actual }}</p>
                    <p class="text-muted-foreground text-xs">
                        your MPG &middot; last {{ fuel.reading_count }} fill-ups
                    </p>
                </div>

                <div v-if="fuel.sticker !== null">
                    <p class="text-h2">{{ fuel.sticker }}</p>
                    <p class="text-muted-foreground text-xs">
                        EPA combined
                        <template v-if="fuel.city && fuel.highway">
                            &middot; {{ fuel.city }} city /
                            {{ fuel.highway }} hwy
                        </template>
                    </p>
                </div>
            </div>

            <p
                v-if="verdict"
                class="text-sm"
                :class="
                    verdict.tone === 'low'
                        ? 'text-brand-on-subtle'
                        : 'text-muted-foreground'
                "
            >
                {{ verdict.text
                }}<template v-if="verdict.tone === 'low'">
                    — worth checking tyre pressures and the air
                    filter.</template
                >
            </p>

            <p
                v-else-if="fuel.actual === null"
                class="text-muted-foreground text-sm"
            >
                Log a few full-tank fill-ups and we'll compare your real economy
                against this.
            </p>
        </CardContent>
    </Card>
</template>
