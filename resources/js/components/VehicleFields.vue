<script setup lang="ts">
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import VehicleColorPicker from '@/components/VehicleColorPicker.vue';
import VehiclePhotoPicker from '@/components/VehiclePhotoPicker.vue';
import type { PaletteColor } from '@/components/VehicleColorPicker.vue';
import type { VehiclePhotoSummary } from '@/types/garage';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

/*
 * The year/make/model block, shared by add and edit.
 *
 * The curated list keeps the data clean enough for Phase 3 to match against
 * NHTSA and EPA responses, and the free-text escape means an unusual vehicle is
 * never a wall in the app's very first flow.
 *
 * Make and model swap between a Select and an Input depending on that mode, but
 * both carry the same `name`, so the submitted payload is identical either way.
 */
const props = defineProps<{
    makes: Record<string, string[]>;
    colors: PaletteColor[];
    minYear: number;
    errors: Record<string, string | undefined>;
    vehicle?: {
        nickname: string | null;
        vin: string | null;
        year: number | null;
        make: string | null;
        model: string | null;
        trim: string | null;
        engine: string | null;
        color: string | null;
        photos?: VehiclePhotoSummary[];
    } | null;
}>();

const makeNames = Object.keys(props.makes);

// Anything already saved that is not in the curated list starts in manual mode,
// so editing an "Other" vehicle does not silently blank its make.
const manual = ref(
    props.vehicle?.make ? !makeNames.includes(props.vehicle.make) : false,
);

const make = ref(props.vehicle?.make ?? '');
const model = ref(props.vehicle?.model ?? '');
/*
 * reka-ui forbids an empty SelectItem value, so "Unknown" needs a sentinel and
 * the real field is a hidden input. Without this the year could be set but
 * never cleared again, even though the column is nullable.
 */
const YEAR_NONE = 'none';
const year = ref(props.vehicle?.year ? String(props.vehicle.year) : YEAR_NONE);
const submittedYear = computed(() =>
    year.value === YEAR_NONE ? '' : year.value,
);

const models = computed(() => props.makes[make.value] ?? []);
const years = computed(() => {
    const latest = new Date().getFullYear() + 1;

    return Array.from(
        { length: latest - props.minYear + 1 },
        (_, index) => latest - index,
    );
});

function onMakeChange() {
    model.value = '';
}
</script>

<template>
    <div class="grid gap-4">
        <div class="grid gap-1.5">
            <Label for="nickname">Nickname</Label>
            <Input
                id="nickname"
                name="nickname"
                placeholder="Optional — “The Wagon”"
                :default-value="vehicle?.nickname ?? undefined"
            />
            <InputError :message="errors.nickname" />
        </div>

        <div v-if="!manual" class="grid grid-cols-2 gap-3">
            <div class="grid min-w-0 gap-1.5">
                <Label for="make">Make</Label>
                <Select
                    v-model="make"
                    name="make"
                    @update:model-value="onMakeChange"
                >
                    <SelectTrigger id="make" class="w-full">
                        <SelectValue placeholder="Choose…" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="name in makeNames"
                            :key="name"
                            :value="name"
                        >
                            {{ name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="grid min-w-0 gap-1.5">
                <Label for="model">Model</Label>
                <Select v-model="model" name="model" :disabled="!make">
                    <SelectTrigger id="model" class="w-full">
                        <SelectValue
                            :placeholder="
                                make ? 'Choose…' : 'Pick a make first'
                            "
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="name in models"
                            :key="name"
                            :value="name"
                        >
                            {{ name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <div v-else class="grid grid-cols-2 gap-3">
            <div class="grid gap-1.5">
                <Label for="make-manual">Make</Label>
                <Input
                    id="make-manual"
                    v-model="make"
                    name="make"
                    required
                    placeholder="Saab"
                />
            </div>
            <div class="grid gap-1.5">
                <Label for="model-manual">Model</Label>
                <Input
                    id="model-manual"
                    v-model="model"
                    name="model"
                    required
                    placeholder="9-3"
                />
            </div>
        </div>

        <InputError :message="errors.make" />
        <InputError :message="errors.model" />

        <Button
            type="button"
            variant="link"
            size="sm"
            class="self-start px-0"
            @click="manual = !manual"
        >
            {{
                manual
                    ? 'Choose from the list instead'
                    : 'Can’t find it? Enter manually'
            }}
        </Button>

        <div class="grid grid-cols-2 gap-3">
            <div class="grid min-w-0 gap-1.5">
                <Label for="year">Year</Label>
                <Select v-model="year">
                    <SelectTrigger id="year" class="w-full">
                        <SelectValue placeholder="Unknown" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="YEAR_NONE">Unknown</SelectItem>
                        <SelectItem
                            v-for="option in years"
                            :key="option"
                            :value="String(option)"
                        >
                            {{ option }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <input type="hidden" name="year" :value="submittedYear" />
                <InputError :message="errors.year" />
            </div>
            <div class="grid gap-1.5">
                <Label for="trim">Trim</Label>
                <Input
                    id="trim"
                    name="trim"
                    placeholder="Optional"
                    :default-value="vehicle?.trim ?? undefined"
                />
            </div>
        </div>

        <VehicleColorPicker
            :colors="colors"
            :default-value="vehicle?.color ?? null"
        />

        <VehiclePhotoPicker :photos="vehicle?.photos ?? []" :errors="errors" />

        <div class="grid gap-1.5">
            <Label for="vin">VIN</Label>
            <Input
                id="vin"
                name="vin"
                placeholder="Optional — unlocks recalls and specs later"
                maxlength="17"
                autocapitalize="characters"
                :default-value="vehicle?.vin ?? undefined"
            />
            <InputError :message="errors.vin" />
        </div>
    </div>
</template>
