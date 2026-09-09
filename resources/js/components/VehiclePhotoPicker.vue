<script setup lang="ts">
import { ImagePlus, Star, X } from '@lucide/vue';
import { computed, onBeforeUnmount, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import type { VehiclePhotoSummary } from '@/types/garage';

/*
 * Photos for a vehicle. The first one is the card image, everywhere.
 *
 * The core mechanic is DataTransfer. Inertia's <Form> submits by running
 * `new FormData(formElement)` over the real DOM, and a FileList is read-only —
 * so after every add, remove or reorder we rebuild a FileList and assign it back
 * to the input. That is what makes `photos[]` arrive in strip order, which in
 * turn is what makes the `new:{index}` tokens in `photo_order[]` resolvable
 * server-side.
 *
 * Existing photos (which have ids) and newly-picked files (which don't) live in
 * one discriminated union, so the strip renders uniformly and TypeScript narrows
 * on `kind` without any casts.
 *
 * Client-side checks here are a courtesy that saves a wasted upload; the server
 * rules remain authoritative and are never skipped.
 */
type PhotoItem =
    | { kind: 'existing'; id: number; url: string; color: string | null }
    | { kind: 'new'; key: string; file: File; url: string };

const props = withDefaults(
    defineProps<{
        photos?: VehiclePhotoSummary[];
        errors?: Record<string, string | undefined>;
        max?: number;
    }>(),
    {
        photos: () => [],
        errors: () => ({}),
        max: 6,
    },
);

const MAX_BYTES = 10 * 1024 * 1024;
const ACCEPTED = ['image/jpeg', 'image/png'];

const fileInput = ref<HTMLInputElement | null>(null);
const rejections = ref<string[]>([]);

/*
 * A plain counter, deliberately not crypto.randomUUID(): that API only exists in
 * a secure context, so it is undefined over plain HTTP on any host other than
 * localhost — which is exactly how this app is served in dev. The key only has
 * to be unique within this component, so there is nothing for crypto to do here.
 */
let nextKey = 0;

const items = ref<PhotoItem[]>(
    props.photos.map((photo) => ({
        kind: 'existing' as const,
        id: photo.id,
        url: photo.url,
        color: photo.color,
    })),
);

const removedIds = ref<number[]>([]);
const dragIndex = ref<number | null>(null);
const isFull = computed(() => items.value.length >= props.max);

/**
 * Index into `photos[]` — i.e. position among the NEW files only, which is what
 * the server resolves a `new:{index}` order token against.
 */
function newIndexOf(item: PhotoItem): number {
    if (item.kind !== 'new') {
        return -1;
    }

    let index = 0;

    for (const candidate of items.value) {
        if (candidate.kind !== 'new') {
            continue;
        }

        if (candidate.key === item.key) {
            return index;
        }

        index += 1;
    }

    return -1;
}

/**
 * Identity that survives reordering. orderToken() must NOT be used as a :key —
 * a new photo's token is its position, so moving it would destroy and rebuild
 * the element, flickering the image and dropping focus.
 */
function stableKey(item: PhotoItem): string {
    return item.kind === 'existing' ? `existing-${item.id}` : item.key;
}

function orderToken(item: PhotoItem): string {
    return item.kind === 'existing'
        ? `existing:${item.id}`
        : `new:${newIndexOf(item)}`;
}

/**
 * Rebuild the input's FileList from the current strip order. A FileList cannot
 * be constructed directly, but DataTransfer can produce one.
 */
function syncInput(): void {
    if (!fileInput.value) {
        return;
    }

    const transfer = new DataTransfer();

    for (const item of items.value) {
        if (item.kind === 'new') {
            transfer.items.add(item.file);
        }
    }

    fileInput.value.files = transfer.files;
}

function onPick(event: Event): void {
    const input = event.target as HTMLInputElement;
    const picked = Array.from(input.files ?? []);

    rejections.value = [];

    for (const file of picked) {
        if (!ACCEPTED.includes(file.type)) {
            rejections.value.push(`${file.name} isn’t a JPEG or PNG.`);
            continue;
        }

        if (file.size > MAX_BYTES) {
            rejections.value.push(`${file.name} is over 10 MB.`);
            continue;
        }

        if (items.value.length >= props.max) {
            rejections.value.push(
                `You can add ${props.max} photos per vehicle.`,
            );
            break;
        }

        items.value.push({
            kind: 'new',
            key: `new-${(nextKey += 1)}`,
            file,
            url: URL.createObjectURL(file),
        });
    }

    syncInput();
}

function remove(index: number): void {
    const [removed] = items.value.splice(index, 1);

    if (!removed) {
        return;
    }

    if (removed.kind === 'existing') {
        removedIds.value.push(removed.id);
    } else {
        // Release the blob immediately; the strip no longer references it.
        URL.revokeObjectURL(removed.url);
    }

    syncInput();
}

/**
 * Move a photo to a new position, clamped. Position 0 is the card image, so
 * this is also how a photo is promoted.
 */
function move(from: number, to: number): void {
    const target = Math.max(0, Math.min(items.value.length - 1, to));

    if (from === target) {
        return;
    }

    const [moved] = items.value.splice(from, 1);

    if (moved) {
        items.value.splice(target, 0, moved);
        syncInput();
    }
}

function onDragStart(index: number): void {
    dragIndex.value = index;
}

/**
 * Reorder live as the pointer passes over a neighbour, rather than only on
 * drop — it makes the new position obvious before letting go.
 */
function onDragEnter(index: number): void {
    if (dragIndex.value === null || dragIndex.value === index) {
        return;
    }

    move(dragIndex.value, index);
    dragIndex.value = index;
}

function makePrimary(index: number): void {
    move(index, 0);
}

// Without this the full file bytes stay alive for the page's lifetime.
onBeforeUnmount(() => {
    for (const item of items.value) {
        if (item.kind === 'new') {
            URL.revokeObjectURL(item.url);
        }
    }
});
</script>

<template>
    <div class="grid gap-2">
        <div class="flex items-center justify-between">
            <Label for="vehicle-photos">Photos</Label>
            <span class="text-muted-foreground text-xs">
                {{ items.length }} of {{ max }}
            </span>
        </div>

        <ul
            v-if="items.length"
            role="list"
            class="grid grid-cols-3 gap-2 sm:grid-cols-4"
        >
            <li
                v-for="(item, index) in items"
                :key="stableKey(item)"
                class="relative"
                :class="dragIndex === index ? 'opacity-50' : ''"
                draggable="true"
                @dragstart="onDragStart(index)"
                @dragenter.prevent="onDragEnter(index)"
                @dragover.prevent
                @dragend="dragIndex = null"
                @drop.prevent="dragIndex = null"
            >
                <button
                    type="button"
                    class="ring-border focus-visible:ring-primary block aspect-square w-full cursor-grab overflow-hidden rounded-lg ring-1 focus-visible:ring-2 focus-visible:outline-none active:cursor-grabbing"
                    :style="
                        item.kind === 'existing' && item.color
                            ? { backgroundColor: item.color }
                            : undefined
                    "
                    :aria-pressed="index === 0"
                    :aria-label="
                        index === 0
                            ? `Photo ${index + 1} of ${items.length}, pinned as the card image. Arrow keys reorder.`
                            : `Photo ${index + 1} of ${items.length}, activate to pin it. Arrow keys reorder.`
                    "
                    @click="makePrimary(index)"
                    @keydown.left.prevent="move(index, index - 1)"
                    @keydown.right.prevent="move(index, index + 1)"
                >
                    <img
                        :src="item.url"
                        alt=""
                        class="size-full object-cover"
                    />
                </button>

                <Badge
                    v-if="index === 0"
                    class="pointer-events-none absolute bottom-2 left-2 gap-1"
                >
                    <Star class="size-3 fill-current" />
                    Pinned
                </Badge>

                <Button
                    type="button"
                    variant="surface"
                    size="icon-sm"
                    class="absolute -top-1.5 -right-1.5"
                    :aria-label="`Remove photo ${index + 1}`"
                    @click="remove(index)"
                >
                    <X />
                </Button>
            </li>
        </ul>

        <div
            v-else
            class="border-border bg-muted/40 grid place-items-center gap-1.5 rounded-lg border-2 border-dashed px-5 py-8 text-center"
        >
            <ImagePlus class="text-muted-foreground size-6" />
            <p class="text-title">No Photos Yet</p>
            <p class="text-muted-foreground text-xs">
                JPEG or PNG, up to 10 MB each. Your first photo becomes the card
                image.
            </p>
        </div>

        <p v-if="items.length > 1" class="text-muted-foreground text-xs">
            Drag to reorder, or tap a photo to pin it as the card image.
        </p>

        <Button
            as-child
            variant="outline"
            size="sm"
            class="self-start"
            :class="isFull ? 'pointer-events-none opacity-45' : ''"
        >
            <label for="vehicle-photos">
                <ImagePlus />
                {{ items.length ? 'Add More' : 'Add Photos' }}
            </label>
        </Button>

        <!--
            accept is the explicit pair, not image/*: with a restrictive accept
            iOS transcodes a HEIC capture to JPEG on selection, whereas image/*
            hands over raw HEIC that then fails server validation with nothing
            useful to tell the user.
        -->
        <input
            id="vehicle-photos"
            ref="fileInput"
            type="file"
            name="photos[]"
            multiple
            accept="image/jpeg,image/png"
            class="sr-only"
            :disabled="isFull"
            @change="onPick"
        />

        <input
            v-for="id in removedIds"
            :key="`removed-${id}`"
            type="hidden"
            name="removed_photo_ids[]"
            :value="id"
        />
        <input
            v-for="(item, index) in items"
            :key="`order-${index}`"
            type="hidden"
            name="photo_order[]"
            :value="orderToken(item)"
        />

        <p
            v-for="message in rejections"
            :key="message"
            class="text-sm text-[var(--danger)]"
        >
            {{ message }}
        </p>

        <InputError :message="errors.photos" />
    </div>
</template>
