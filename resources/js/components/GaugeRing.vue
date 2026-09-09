<script setup lang="ts">
import { computed } from 'vue';
import type { GaugeStatus } from '@/types/gauges';

/*
 * A service gauge: a segmented ring filling toward "due".
 *
 * Ported from docs/design_system/components/data/ScoreGauge.jsx, which draws a
 * segmented SVG arc rather than a smooth stroke — that segmentation is the
 * design system's signature, so it is kept. Closed to a full circle instead of
 * the kit's semicircle, because these have to read at 44px on a garage card,
 * not just at 250px as a hero.
 *
 * Colour comes from status, per the brief: healthy / soon / overdue. Grey means
 * uncalibrated — we do not know when it was last done, which is NOT the same as
 * being fine, and must never look like being overdue either.
 */
const props = withDefaults(
    defineProps<{
        progress: number;
        status: GaugeStatus;
        size?: number;
        segments?: number;
        thickness?: number;
    }>(),
    {
        size: 64,
        segments: 24,
        thickness: 6,
    },
);

const GAP = 0.06; // radians between segments

const trackColor = 'var(--border-strong)';

const fillColor = computed(() => {
    switch (props.status) {
        case 'healthy':
            return 'var(--success)';
        case 'soon':
            return 'var(--warning)';
        case 'due':
        case 'overdue':
            return 'var(--brand)';
        default:
            return trackColor;
    }
});

const filledCount = computed(() =>
    props.status === 'uncalibrated'
        ? 0
        : Math.round(Math.max(0, Math.min(1, props.progress)) * props.segments),
);

/** Pre-computed wedge paths, drawn from 12 o'clock clockwise. */
const arcs = computed(() => {
    const centre = props.size / 2;
    const outer = centre - 1;
    const inner = outer - props.thickness;
    const step = (Math.PI * 2) / props.segments;

    return Array.from({ length: props.segments }, (_, index) => {
        const start = -Math.PI / 2 + index * step + GAP / 2;
        const end = -Math.PI / 2 + (index + 1) * step - GAP / 2;

        const point = (radius: number, angle: number) =>
            `${centre + radius * Math.cos(angle)} ${centre + radius * Math.sin(angle)}`;

        return {
            key: index,
            on: index < filledCount.value,
            d: [
                `M ${point(outer, start)}`,
                `A ${outer} ${outer} 0 0 1 ${point(outer, end)}`,
                `L ${point(inner, end)}`,
                `A ${inner} ${inner} 0 0 0 ${point(inner, start)}`,
                'Z',
            ].join(' '),
        };
    });
});
</script>

<template>
    <div
        class="relative shrink-0"
        :style="{ width: `${size}px`, height: `${size}px` }"
    >
        <svg
            :width="size"
            :height="size"
            :viewBox="`0 0 ${size} ${size}`"
            class="block"
            aria-hidden="true"
        >
            <path
                v-for="arc in arcs"
                :key="arc.key"
                :d="arc.d"
                :fill="arc.on ? fillColor : trackColor"
                :fill-opacity="arc.on ? 1 : 0.35"
            />
        </svg>

        <div class="absolute inset-0 flex items-center justify-center">
            <slot />
        </div>
    </div>
</template>
