<script setup lang="ts">
import type { NavigationMenuLinkEmits, NavigationMenuLinkProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import {
  NavigationMenuLink,
  useForwardPropsEmits,
} from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<NavigationMenuLinkProps & { class?: HTMLAttributes["class"] }>()
const emits = defineEmits<NavigationMenuLinkEmits>()

const delegatedProps = reactiveOmit(props, "class")
const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <NavigationMenuLink
    data-slot="navigation-menu-link"
    v-bind="forwarded"
    :class="cn('data-active:focus:bg-hover-surface data-active:hover:bg-hover-surface data-active:bg-hover-surface/50 data-active:text-hover-surface-foreground hover:bg-hover-surface hover:text-hover-surface-foreground focus:bg-hover-surface focus:text-hover-surface-foreground ring-ring/10 outline-ring/50 [&_svg:not([class*=\'text-\'])]:text-muted-foreground flex flex-col gap-1 rounded-sm p-2 text-sm transition-[color,box-shadow] focus-visible:ring-4 focus-visible:outline-1 [&_svg:not([class*=\'size-\'])]:size-4', props.class)"
  >
    <slot />
  </NavigationMenuLink>
</template>
