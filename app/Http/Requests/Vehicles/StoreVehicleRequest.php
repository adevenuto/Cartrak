<?php

namespace App\Http\Requests\Vehicles;

use App\Concerns\HandlesVehiclePhotos;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    use HandlesVehiclePhotos;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nickname' => ['nullable', 'string', 'max:60'],

            // A VIN is optional in Phase 1 and purely stored: decoding it needs
            // NHTSA vPIC, which arrives in Phase 3. 17 characters, and I/O/Q are
            // never used in a VIN precisely because they look like 1/0.
            'vin' => ['nullable', 'string', 'size:17', 'regex:/^[A-HJ-NPR-Z0-9]{17}$/i'],

            'year' => ['nullable', 'integer', 'min:'.config('vehicles.min_year'), 'max:'.(now()->year + 1)],
            'make' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:60'],
            'trim' => ['nullable', 'string', 'max:60'],
            'engine' => ['nullable', 'string', 'max:60'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            // The one thing adding a car genuinely requires: without a starting
            // reading there is nothing to measure future intervals against.
            'odometer' => ['required', 'integer', 'min:0', 'max:2000000'],

            ...$this->photoRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'vin.size' => 'A VIN is exactly 17 characters.',
            'vin.regex' => 'That does not look like a valid VIN. VINs never contain I, O or Q.',
            'odometer.required' => 'Enter the current odometer reading so your gauges start from the right place.',

            ...$this->photoMessages(),
        ];
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
