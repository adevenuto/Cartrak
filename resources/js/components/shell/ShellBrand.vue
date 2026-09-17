<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { computed } from 'vue';
import { garage } from '@/routes';

/*
 * The lockup: logo chip plus the wordmark.
 *
 * Two arrangements. `inline` is the shell's — mark on the left, wordmark on two
 * condensed lines beside it. `stacked` centres a larger mark with the name on
 * one line beneath, which is what the auth screens want: there is no chrome
 * around them, so the lockup is the only thing identifying the product.
 *
 * The light logo variant is the one with navy ink lifted to paper, so the mark
 * reads as a line drawing on the navy shell. Design doc §1 is explicit that the
 * mark never sits on a filled chip or box.
 *
 * `href` exists because this renders an anchor. The auth layout used to wrap it
 * in a second <Link>, which nests one anchor inside another — invalid HTML, and
 * browsers recover from it unpredictably.
 */
const props = withDefaults(
    defineProps<{
        compact?: boolean;
        ground?: 'navy' | 'paper';
        orientation?: 'inline' | 'stacked';
        href?: NonNullable<InertiaLinkProps['href']>;
    }>(),
    {
        compact: false,
        ground: 'navy',
        orientation: 'inline',
        href: undefined,
    },
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

const stacked = computed(() => props.orientation === 'stacked');

const target = computed(() => props.href ?? garage());
</script>

<template>
    <Link
        :href="target"
        :class="
            stacked
                ? 'flex flex-col items-center gap-(--space-3)'
                : compact
                  ? 'flex flex-none items-center gap-[11px]'
                  : 'flex w-[194px] flex-none items-center gap-[11px]'
        "
    >
        <img
            :src="mark"
            alt=""
            class="block flex-none"
            :class="stacked ? 'w-20' : 'w-12'"
        />

        <span
            v-if="stacked"
            class="font-display text-[22px]/none font-semibold tracking-[0.06em] uppercase"
            :style="{ color: ink }"
        >
            Ignition Index
        </span>

        <span
            v-else-if="!compact"
            class="font-display text-[20px]/[1.05] font-semibold tracking-[0.02em] uppercase"
            :style="{ color: ink }"
        >
            Ignition<br />Index
        </span>

        <span class="sr-only">Ignition Index — home</span>
    </Link>
</template>
