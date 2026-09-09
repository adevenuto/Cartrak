<script setup lang="ts">
import { Check, X } from '@lucide/vue';
import type { AcceptableValue } from 'reka-ui';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';

/*
 * Paint colour for a vehicle, so the garage is scannable at a glance.
 *
 * Swatches are a ToggleGroup — single-select, keyboard navigable, and the same
 * primitive the quick-add lanes use. The custom option is a native
 * <input type="color">: it is the only way to reach the OS colour picker, and
 * there is no shadcn equivalent, so it is deliberately raw and styled to match
 * the swatches.
 *
 * Colour is never the sole carrier of meaning — every swatch is labelled and
 * the dot in the garage always sits beside the vehicle's name.
 */
export type PaletteColor = { name: string; hex: string };

const props = defineProps<{
    colors: PaletteColor[];
    defaultValue?: string | null;
}>();

const color = ref<string>(props.defaultValue ?? '');

const isCustom = computed(
    () =>
        color.value !== '' &&
        !props.colors.some((swatch) => swatch.hex === color.value),
);

const selectedName = computed(
    () =>
        props.colors.find((swatch) => swatch.hex === color.value)?.name ??
        (isCustom.value ? color.value.toUpperCase() : 'None'),
);

/**
 * A single-select ToggleGroup emits an empty value when the active swatch is
 * pressed again — here that is a meaningful "clear the colour", so it is kept.
 */
function onSwatchChange(value: AcceptableValue | AcceptableValue[]) {
    color.value = typeof value === 'string' ? value : '';
}

function onCustomInput(event: Event) {
    color.value = (event.target as HTMLInputElement).value.toLowerCase();
}
</script>

<template>
    <div class="grid gap-2">
        <div class="flex items-center justify-between">
            <Label for="vehicle-color">Colour</Label>
            <span class="text-muted-foreground text-xs">
                {{ selectedName }}
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <ToggleGroup
                type="single"
                :model-value="color"
                class="flex-wrap gap-2 rounded-none bg-transparent p-0"
                aria-label="Paint colour"
                @update:model-value="onSwatchChange"
            >
                <ToggleGroupItem
                    v-for="swatch in colors"
                    :key="swatch.hex"
                    :value="swatch.hex"
                    :aria-label="swatch.name"
                    :title="swatch.name"
                    class="ring-border data-[state=on]:ring-primary size-9 rounded-full p-0 ring-1 hover:bg-transparent data-[state=on]:ring-2 data-[state=on]:ring-offset-2 data-[state=on]:ring-offset-[var(--card)]"
                    :style="{ backgroundColor: swatch.hex }"
                >
                    <Check
                        v-if="color === swatch.hex"
                        class="size-4"
                        :style="{
                            color:
                                swatch.hex === '#f4f4f5' ||
                                swatch.hex === '#d6c7a8' ||
                                swatch.hex === '#c0c4c8'
                                    ? '#1b1b1d'
                                    : '#ffffff',
                        }"
                    />
                </ToggleGroupItem>
            </ToggleGroup>

            <!--
                Native colour input: the OS picker has no shadcn equivalent, and
                a hand-rolled one would be worse than the platform's.
            -->
            <label
                class="ring-border focus-within:ring-primary relative size-9 shrink-0 cursor-pointer overflow-hidden rounded-full ring-1 focus-within:ring-2"
                :class="isCustom ? 'ring-primary ring-2' : ''"
                :style="
                    isCustom
                        ? { backgroundColor: color }
                        : {
                              background:
                                  'conic-gradient(#b31d26, #c9a227, #1e6f45, #1f4e9c, #6a1520, #b31d26)',
                          }
                "
                title="Custom colour"
            >
                <span class="sr-only">Choose a custom colour</span>
                <input
                    id="vehicle-color"
                    type="color"
                    class="absolute inset-0 size-full cursor-pointer opacity-0"
                    :value="color || '#c0c4c8'"
                    @input="onCustomInput"
                />
            </label>

            <Button
                v-if="color"
                type="button"
                variant="ghost"
                size="icon"
                aria-label="Clear colour"
                @click="color = ''"
            >
                <X />
            </Button>
        </div>

        <input type="hidden" name="color" :value="color" />
    </div>
</template>
