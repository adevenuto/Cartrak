<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppWordmark from '@/components/AppWordmark.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 px-5 py-10"
    >
        <div class="w-full max-w-[var(--content-max)]">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <AppWordmark class="mb-1" />
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-h1">{{ title }}</h1>
                        <p class="text-muted-foreground text-body text-center">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
