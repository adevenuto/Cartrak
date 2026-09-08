<script setup lang="ts">
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

/*
 * Replaces the starter's AppSidebarHeader.
 *
 * The SidebarTrigger is md-and-up ONLY, and that is load-bearing: it is how the
 * shadcn sidebar's mobile Sheet is suppressed below md. The Sheet branch still
 * renders, but nothing can open it because no trigger exists at that width.
 *
 * Mobile gets the design system's screen-title treatment instead of breadcrumbs,
 * which do not belong on a 390px-wide thumb-driven surface.
 */
const props = withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const title = computed(() => props.breadcrumbs.at(-1)?.title ?? '');
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 px-5 md:px-(--card-pad)"
    >
        <div class="flex min-w-0 items-center gap-2">
            <SidebarTrigger class="-ml-1 hidden md:inline-flex" />

            <h1 v-if="title" class="text-h2 truncate md:hidden">
                {{ title }}
            </h1>

            <template v-if="breadcrumbs.length > 0">
                <Breadcrumbs
                    class="hidden md:flex"
                    :breadcrumbs="breadcrumbs"
                />
            </template>
        </div>
    </header>
</template>
