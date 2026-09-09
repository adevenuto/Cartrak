<?php

namespace App\Http\Requests\Vehicles;

use App\Concerns\HandlesVehiclePhotos;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Validator;

class UpdateVehicleRequest extends FormRequest
{
    use HandlesVehiclePhotos;

    /**
     * Editing a vehicle deliberately does NOT accept an odometer: readings only
     * ever enter through events, so the spine stays the single source of truth.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nickname' => ['nullable', 'string', 'max:60'],
            'vin' => ['nullable', 'string', 'size:17', 'regex:/^[A-HJ-NPR-Z0-9]{17}$/i'],
            'year' => ['nullable', 'integer', 'min:'.config('vehicles.min_year'), 'max:'.(now()->year + 1)],
            'make' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:60'],
            'trim' => ['nullable', 'string', 'max:60'],
            'engine' => ['nullable', 'string', 'max:60'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            ...$this->photoRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->photoMessages();
    }

    /**
     * The real cap is `existing - removed + new <= max`, which is not knowable
     * at rules() time because removed_photo_ids is part of the same payload.
     * The array `max:` rule only bounds the new files.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $vehicle = $this->routeVehicle();

            if ($vehicle === null) {
                return;
            }

            $max = Config::integer('vehicles.photos.max_per_vehicle');
            $existing = $vehicle->photos()->count();

            // Count removals through a vehicle-scoped query, so a forged id for
            // someone else's photo cannot inflate the allowance.
            $removing = $vehicle->photos()
                ->whereIn('id', $this->removedPhotoIds())
                ->count();

            if ($existing - $removing + count($this->photos()) > $max) {
                $validator->errors()->add('photos', __(
                    'A vehicle can hold :max photos. Remove one before adding another.',
                    ['max' => $max],
                ));
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('vin'))) {
            $this->merge(['vin' => strtoupper(trim($this->string('vin')->value()))]);
        }

        if (is_string($this->input('color'))) {
            $this->merge(['color' => strtolower(trim($this->string('color')->value()))]);
        }
    }
}
