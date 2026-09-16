<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { BlueprintFrame } from "@/components/ui/blueprint"
import { cn } from "@/lib/utils"

/*
 * Card delegates to BlueprintFrame so every existing consumer picks up the
 * square hairline frame and its corner marks without touching a single page.
 *
 * The previous implementation carried `border-0`, which actively fought the
 * blueprint hairline even after the radius and shadow scales were zeroed.
 */
const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <BlueprintFrame
    data-slot="card"
    :class="
      cn(
        'text-card-foreground flex flex-col gap-(--space-4) py-(--card-pad)',
        props.class,
      )
    "
  >
    <slot />
  </BlueprintFrame>
</template>
