<script setup lang="ts">
/*
 * The paint-colour chip shown beside a vehicle's name.
 *
 * A SQUARE, not a dot. Design doc §7: the owner's identity colour is "the one
 * place a non-system color is allowed, and only as a 10px square" — a circle
 * would be both the wrong geometry (§4 bans round shapes) and the wrong
 * semantics, since every other square of colour in this system means status.
 *
 * Colour is never the only identifier — it always sits next to the name — so
 * this is decorative to assistive tech unless a name is known, in which case it
 * gets a label rather than being announced as a bare graphic.
 *
 * The hairline matters: a white or silver car on a paper card would otherwise
 * have no edge at all.
 */
withDefaults(
    defineProps<{
        color?: string | null;
        label?: string | null;
        size?: 'sm' | 'md';
    }>(),
    {
        color: null,
        label: null,
        size: 'md',
    },
);
</script>

<template>
    <span
        v-if="color"
        class="inline-block shrink-0 ring-1 ring-(--color-divider)"
        :class="size === 'sm' ? 'size-[7px]' : 'size-[10px]'"
        :style="{ backgroundColor: color }"
        :title="label ?? undefined"
        :aria-hidden="label ? undefined : true"
        :aria-label="label ?? undefined"
        :role="label ? 'img' : undefined"
    />
</template>
