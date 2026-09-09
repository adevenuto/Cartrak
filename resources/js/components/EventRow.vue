<script setup lang="ts">
import { DollarSign, Fuel, Gauge, Wrench } from '@lucide/vue';
import { computed } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import { formatCents, formatDate, formatMiles } from '@/lib/format';
import type { VehicleEvent } from '@/types/garage';

/*
 * One row of history. Every event shows the same spine — date and odometer —
 * with the lane-specific detail underneath, so a mixed list still scans.
 */
const props = defineProps<{
    event: VehicleEvent;
    vehicleName?: string;
}>();

const icons = {
    visit: Wrench,
    fuel: Fuel,
    expense: DollarSign,
    odometer: Gauge,
} as const;

const detail = computed(() => {
    const event = props.event;

    if (event.type === 'visit') {
        return (
            event.line_items.map((item) => item.name).join(', ') ||
            event.location ||
            'Service visit'
        );
    }

    if (event.type === 'fuel') {
        const parts = [];
        if (event.gallons !== null) parts.push(`${event.gallons} gal`);
        if (event.mpg !== null) parts.push(`${event.mpg} MPG`);

        return parts.join(' · ') || 'Fuel';
    }

    if (event.type === 'expense') {
        return event.category ?? 'Expense';
    }

    return event.notes ?? 'Odometer reading';
});

const secondary = computed(() =>
    [props.event.location, props.event.notes].filter(Boolean).join(' — '),
);
</script>

<template>
    <li>
        <Card class="shadow-sm">
            <CardContent class="flex items-start gap-3">
                <span
                    class="bg-brand-subtle text-brand-on-subtle flex size-10 shrink-0 items-center justify-center rounded-full"
                >
                    <component :is="icons[event.type]" class="size-5" />
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex items-baseline justify-between gap-2">
                        <p class="text-title truncate">{{ detail }}</p>
                        <p
                            v-if="event.cost_cents !== null"
                            class="text-title shrink-0"
                        >
                            {{ formatCents(event.cost_cents) }}
                        </p>
                    </div>

                    <p class="text-muted-foreground mt-0.5 text-sm">
                        <span v-if="vehicleName">{{ vehicleName }} · </span>
                        {{ formatDate(event.occurred_on) }} ·
                        {{ formatMiles(event.odometer) }}
                    </p>

                    <p
                        v-if="secondary && event.type !== 'odometer'"
                        class="text-muted-foreground mt-1 truncate text-sm"
                    >
                        {{ secondary }}
                    </p>
                </div>
            </CardContent>
        </Card>
    </li>
</template>
