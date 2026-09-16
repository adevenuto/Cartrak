<script setup lang="ts">
import { ShieldAlert } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { formatDate } from '@/lib/format';
import type { VehicleRecall } from '@/types/gauges';

/*
 * Open NHTSA safety recalls for a vehicle.
 *
 * Given prominence above the gauges deliberately: a recall is a safety notice
 * from the manufacturer, not a maintenance countdown, and the work is carried
 * out free of charge — which is worth saying, because plenty of people assume
 * otherwise and put it off.
 *
 * Free forever per the brief, and the best word-of-mouth hook the product has.
 */
defineProps<{
    recalls: VehicleRecall[];
}>();
</script>

<template>
    <Card v-if="recalls.length" class="border-0">
        <CardContent class="flex flex-col gap-4">
            <div class="flex items-center gap-2.5">
                <ShieldAlert class="text-accent-700 size-5 shrink-0" />
                <h2 class="text-h3">
                    {{ recalls.length }} open safety
                    {{ recalls.length === 1 ? 'recall' : 'recalls' }}
                </h2>
            </div>

            <ul role="list" class="flex flex-col gap-4">
                <li
                    v-for="recall in recalls"
                    :key="recall.id"
                    class="border-border border-t pt-4 first:border-t-0 first:pt-0"
                >
                    <div class="mb-1 flex flex-wrap items-center gap-2">
                        <p class="text-title">
                            {{ recall.component ?? 'Safety recall' }}
                        </p>
                        <Badge variant="secondary">
                            {{ recall.campaign_number }}
                        </Badge>
                    </div>

                    <p
                        v-if="recall.summary"
                        class="text-muted-foreground text-sm"
                    >
                        {{ recall.summary }}
                    </p>

                    <p v-if="recall.remedy" class="text-body mt-2">
                        {{ recall.remedy }}
                    </p>

                    <p
                        v-if="recall.reported_on"
                        class="text-muted-foreground mt-1 text-xs"
                    >
                        Reported {{ formatDate(recall.reported_on) }}
                    </p>
                </li>
            </ul>

            <p class="text-muted-foreground text-xs">
                Recall work is carried out free of charge by a franchised
                dealer.
            </p>
        </CardContent>
    </Card>
</template>
