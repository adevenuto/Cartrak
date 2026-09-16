<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { garage } from '@/routes';

/*
 * The lockup: logo chip + the wordmark on two condensed uppercase lines.
 *
 * The light logo variant is the one with navy ink lifted to paper, so the mark
 * reads as a line drawing on the navy shell. Design doc §1 is explicit that the
 * mark never sits on a filled chip or box — the previous wordmark put it on a
 * brand-coloured tile, which is exactly what not to do.
 */
const props = withDefaults(
    defineProps<{ compact?: boolean; ground?: 'navy' | 'paper' }>(),
    { compact: false, ground: 'navy' },
);

// §1: the light variant is for the navy shell only; on paper the mark is used
// as-is, in navy ink.
const mark = computed(() =>
    props.ground === 'navy'
        ? '/img/ignition-index-logo-light.png'
        : '/img/ignition-index-logo.png',
);

const ink = computed(() =>
    props.ground === 'navy' ? 'var(--shell-ink)' : 'var(--color-text)',
);
</script>

<template>
    <Link
        :href="garage()"
        class="flex flex-none items-center gap-[11px]"
        :class="compact ? '' : 'w-[194px]'"
    >
        <img :src="mark" alt="" class="block w-12 flex-none" />
        <span
            v-if="!compact"
            class="font-display text-[20px]/[1.05] font-semibold tracking-[0.02em] uppercase"
            :style="{ color: ink }"
        >
            Ignition<br />Index
        </span>
        <span class="sr-only">Ignition Index — go to garage</span>
    </Link>
</template>
