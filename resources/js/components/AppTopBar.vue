<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Bell } from '@lucide/vue';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { notifications } from '@/routes';
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

const page = usePage();
const unread = computed(() => page.props.unreadNotifications ?? 0);
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

        <!-- The in-app record of everything we've sent, always reachable. -->
        <Button
            as-child
            variant="ghost"
            size="icon-tap"
            class="text-muted-foreground hover:text-foreground relative ml-auto"
        >
            <Link :href="notifications()">
                <Bell />
                <span
                    v-if="unread"
                    class="bg-primary text-primary-foreground absolute top-1.5 right-1.5 flex min-w-4 items-center justify-center rounded-full px-1 text-[10px] leading-4 font-bold"
                >
                    {{ unread > 9 ? '9+' : unread }}
                </span>
                <span class="sr-only">
                    Notifications{{ unread ? `, ${unread} unread` : '' }}
                </span>
            </Link>
        </Button>
    </header>
</template>
