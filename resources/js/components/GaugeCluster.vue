<script setup lang="ts">
import { computed } from 'vue';
import GaugeDial from '@/components/GaugeDial.vue';
import { gaugeInk, gaugeReadout } from '@/lib/gauge';
import { Card, CardContent } from '@/components/ui/card';
import type { Gauge } from '@/types/gauges';

/*
 * The per-vehicle gauge cluster: one ring per scheduled item, each counting
 * down on whichever axis binds first.
 *
 * Every tile is a button, not just the uncalibrated ones — adjusting an
 * interval or correcting a last-done is the same job whether the gauge is
 * already running or not, and hiding that behind one small link on uncalibrated
 * items made it nearly undiscoverable.
 *
 * Sorted by the server with the most urgent first and uncalibrated items last,
 * because those are a prompt to act rather than a countdown.
 */
const props = defineProps<{
    gauges: Gauge[];
}>();

const emit = defineEmits<{
    select: [gauge: Gauge];
}>();

const uncalibrated = computed(() =>
    props.gauges.filter((gauge) => gauge.status === 'uncalibrated'),
);
</script>

<template>
    <ul role="list" class="grid grid-cols-2 gap-3 sm:grid-cols-3">
        <li v-for="gauge in gauges" :key="gauge.id">
            <button
                type="button"
                class="ease-standard block h-full w-full text-left transition-colors duration-[var(--dur-fast)]"
                :aria-label="`${gauge.name}, ${gauge.label}. Adjust.`"
                @click="emit('select', gauge)"
            >
                <Card class="hover:ring-border h-full hover:ring-1">
                    <CardContent
                        class="flex flex-col items-center gap-2 text-center"
                    >
                        <GaugeDial
                            class="h-[72px] w-[94px]"
                            :percent="gauge.raw_progress * 100"
                            :status="gauge.status"
                        />

                        <p
                            class="font-display text-metric leading-none"
                            :style="{ color: gaugeInk(gauge.status) }"
                        >
                            {{
                                gaugeReadout(
                                    gauge.status,
                                    gauge.raw_progress * 100,
                                )
                            }}
                        </p>

                        <div class="w-full min-w-0">
                            <p class="text-title truncate">{{ gauge.name }}</p>
                            <p class="text-muted-foreground truncate text-xs">
                                {{ gauge.label }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </button>
        </li>
    </ul>

    <p v-if="uncalibrated.length" class="text-muted-foreground text-sm">
        {{ uncalibrated.length }}
        {{ uncalibrated.length === 1 ? 'item is' : 'items are' }} waiting on a
        last-done date. Tap one to set it.
    </p>
</template>
