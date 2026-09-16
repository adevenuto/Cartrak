<script setup lang="ts">
import type { Component, HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

/*
 * A blueprint object: square, hairline-bordered, with the four `+` registration
 * marks at its corners.
 *
 * Design doc section 4 says never to omit the marks from a framed element. A
 * component makes that structurally impossible rather than a thing a reviewer
 * has to notice, which is the whole reason this exists instead of four
 * hand-written spans per card.
 *
 * The marks are four children rather than pseudo-elements because each mark
 * needs two strokes and an element only has two pseudo-elements. Industry's own
 * sheet does the same.
 */
const props = withDefaults(
  defineProps<{
    as?: string | Component
    class?: HTMLAttributes["class"]
    /** Interactive frames take the steel border on hover, as gauge cards do. */
    interactive?: boolean
  }>(),
  { as: "div", interactive: false },
)
</script>

<template>
  <component
    :is="props.as"
    data-slot="blueprint"
    :class="
      cn(
        'ii-blueprint',
        interactive &&
          'hover:border-accent transition-colors duration-[var(--dur-fast)] ease-standard',
        props.class,
      )
    "
  >
    <i class="ii-corner ii-corner-tl" aria-hidden="true" />
    <i class="ii-corner ii-corner-tr" aria-hidden="true" />
    <i class="ii-corner ii-corner-bl" aria-hidden="true" />
    <i class="ii-corner ii-corner-br" aria-hidden="true" />
    <slot />
  </component>
</template>
