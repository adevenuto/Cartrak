<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ShellDueNext from '@/components/shell/ShellDueNext.vue';
import ShellNavItem from '@/components/shell/ShellNavItem.vue';
import ShellUpgradeCell from '@/components/shell/ShellUpgradeCell.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { useShellNav } from '@/lib/shell-nav';

/*
 * The rail's contents, without the rail's own chrome — so the desktop column
 * and the mobile drawer render exactly the same thing and cannot drift.
 */
const nav = useShellNav();
const page = usePage();
const { isCurrentOrParentUrl } = useCurrentUrl();

const hasDueNext = computed(
    () => (page.props.garage?.due_next ?? []).length > 0,
);
</script>

<template>
    <div class="flex h-full flex-col px-[14px] py-[22px]">
        <p
            class="ii-eyebrow px-2 pb-3 tracking-[0.16em] text-[var(--shell-ink-eyebrow)]"
        >
            Workspace
        </p>

        <nav class="flex flex-col gap-[3px]">
            <ShellNavItem
                v-for="item in nav"
                :key="item.title"
                :item="item"
                :active="isCurrentOrParentUrl(item.href)"
            />
        </nav>

        <template v-if="hasDueNext">
            <div class="h-[30px]" aria-hidden="true" />
            <p
                class="ii-eyebrow px-2 pb-3 tracking-[0.16em] text-[var(--shell-ink-eyebrow)]"
            >
                Due next
            </p>
            <ShellDueNext />
        </template>

        <div class="min-h-10 flex-1" aria-hidden="true" />

        <ShellUpgradeCell />
    </div>
</template>
