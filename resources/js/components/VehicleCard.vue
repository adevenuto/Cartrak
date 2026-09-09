<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Car, ChevronRight } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import VehicleColorDot from '@/components/VehicleColorDot.vue';
import { formatDate, formatMiles } from '@/lib/format';
import { show } from '@/routes/vehicles';
import type { GarageVehicle } from '@/types/garage';

/*
 * Ported from docs/design_system/components/data/VehicleCard.jsx: white card,
 * 22px radius, name on the left with a glyph slot on the right, a
 * hairline-divided spec row beneath.
 *
 * Two departures from the kit, both deliberate:
 *   - its two-button "Rent Now / Detail" footer is rental copy left over from
 *     the screenshot the design system was derived from. The whole card is the
 *     tap target here instead.
 *   - the spec row carries the odometer, which is the number this product is
 *     actually about.
 *
 * The overall-health ring the brief wants on this card needs the two-axis
 * computation, so it arrives in Phase 2.
 */
const props = defineProps<{
    vehicle: GarageVehicle;
}>();

// A deleted file, an expired session, or being offline all land here — fall
// back to the glyph rather than showing broken-image chrome.
const failed = ref(false);

const specs = computed(() =>
    [
        props.vehicle.year,
        props.vehicle.make,
        props.vehicle.model,
        props.vehicle.trim,
    ]
        .filter(Boolean)
        .join(' '),
);
</script>

<template>
    <Link
        :href="show(vehicle.id)"
        class="ease-standard block transition-transform duration-[var(--dur-fast)] active:scale-[var(--press-scale)]"
        :data-test="`vehicle-card-${vehicle.id}`"
    >
        <Card>
            <CardContent>
                <div class="flex items-start gap-3">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-h3 flex items-center gap-2">
                            <VehicleColorDot
                                :color="vehicle.color"
                                :label="`Paint colour ${vehicle.color}`"
                            />
                            <span class="truncate">{{ vehicle.name }}</span>
                        </h3>
                        <p
                            v-if="specs"
                            class="text-muted-foreground mt-0.5 truncate text-sm"
                        >
                            {{ specs }}
                        </p>
                    </div>

                    <span
                        class="bg-muted text-muted-foreground flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-md"
                        :style="
                            vehicle.photo_color
                                ? { backgroundColor: vehicle.photo_color }
                                : undefined
                        "
                    >
                        <img
                            v-if="vehicle.photo_thumb_url && !failed"
                            :src="vehicle.photo_thumb_url"
                            :alt="`Photo of ${vehicle.name}`"
                            width="48"
                            height="48"
                            loading="lazy"
                            decoding="async"
                            class="size-full object-cover"
                            @error="failed = true"
                        />

                        <Car v-else class="size-6" />
                    </span>
                </div>

                <div
                    class="border-border mt-4 flex items-center justify-between border-t pt-3"
                >
                    <div class="min-w-0">
                        <p class="text-title">
                            {{
                                formatMiles(vehicle.last_odometer) ??
                                'No reading yet'
                            }}
                        </p>
                        <p
                            v-if="vehicle.last_odometer_at"
                            class="text-muted-foreground text-xs"
                        >
                            as of {{ formatDate(vehicle.last_odometer_at) }}
                        </p>
                    </div>

                    <ChevronRight
                        class="text-muted-foreground size-5 shrink-0"
                    />
                </div>
            </CardContent>
        </Card>
    </Link>
</template>
