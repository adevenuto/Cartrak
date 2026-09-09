<script setup lang="ts">
import type { AcceptableValue } from 'reka-ui';
import { Monitor, Moon, Sun } from '@lucide/vue';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { useAppearance } from '@/composables/useAppearance';
import type { Appearance } from '@/types';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;

/**
 * A single-select ToggleGroup emits an empty value when the active item is
 * pressed again; appearance always has a value, so that is ignored.
 */
function onChange(value: AcceptableValue | AcceptableValue[]) {
    if (typeof value === 'string' && value !== '') {
        updateAppearance(value as Appearance);
    }
}
</script>

<template>
    <ToggleGroup
        type="single"
        :model-value="appearance"
        class="bg-muted rounded-full p-1"
        aria-label="Appearance"
        @update:model-value="onChange"
    >
        <ToggleGroupItem
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            :value="value"
            :aria-label="label"
            class="data-[state=on]:bg-card data-[state=on]:text-foreground text-muted-foreground h-9 rounded-full px-3.5 data-[state=on]:shadow-xs"
        >
            <component :is="Icon" />
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </ToggleGroupItem>
    </ToggleGroup>
</template>
