<script setup lang="ts">
/*
 * The paint-colour dot shown beside a vehicle's name.
 *
 * Colour is never the only identifier — it always sits next to the name — so
 * this is decorative to assistive tech unless a name is known, in which case it
 * gets a label rather than being announced as a bare graphic.
 *
 * The ring matters: a white or silver car on a white card would otherwise have
 * no edge at all.
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
        class="ring-border inline-block shrink-0 rounded-full ring-1"
        :class="size === 'sm' ? 'size-3' : 'size-4'"
        :style="{ backgroundColor: color }"
        :title="label ?? undefined"
        :aria-hidden="label ? undefined : true"
        :aria-label="label ?? undefined"
        :role="label ? 'img' : undefined"
    />
</template>
