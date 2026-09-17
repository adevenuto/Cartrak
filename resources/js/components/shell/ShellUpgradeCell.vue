<script setup lang="ts">
import { Truck } from '@lucide/vue';
</script>

<template>
    <!--
      Sits at the foot of the rail. Not a Link yet: there is no Fleet tier to
      route to, and a dead href is worse than an honest button.
    -->
    <!--
      The resting border is a class, not an inline style: an inline style beats
      any class selector, so hover:border-accent could never take effect.
    -->
    <button
        type="button"
        class="ease-standard hover:border-accent flex items-center gap-(--space-3) border border-[var(--shell-hairline)] p-3 text-left text-[13px] text-[var(--shell-ink-secondary)] transition-colors duration-[var(--dur-fast)] hover:text-[var(--shell-ink)]"
    >
        <Truck class="size-4 flex-none" :stroke-width="1.5" />
        Upgrade to Fleet
    </button>
</template>
