<?php

namespace App\Services\Nhtsa;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * NHTSA vPIC (VIN decode) and the recalls API.
 *
 * Both are free, keyless and US-only. Neither is a hard dependency: every
 * method here returns null or an empty array when NHTSA is slow, broken or
 * unreachable, and the caller carries on. The brief is explicit that the app
 * must be fully usable with every external API down, so nothing in here is
 * allowed to throw.
 *
 * vPIC in particular is slow — 2 to 5 seconds is normal — which is why decoding
 * happens in a queued job and never in a request.
 */
class NhtsaClient
{
    /**
     * Decode a VIN into the fields we care about, plus the raw response.
     *
     * @return array{specs: array<string, string>, raw: array<string, mixed>}|null
     */
    public function decodeVin(string $vin): ?array
    {
        $response = $this->get(
            Config::string('services.nhtsa.vpic_url')."/decodevinvalues/{$vin}",
            ['format' => 'json'],
        );

        $result = $response['Results'][0] ?? null;

        if (! is_array($result)) {
            return null;
        }

        // vPIC returns ~140 fields, most of them empty strings. Keep the whole
        // thing cached on the vehicle (Phase 6's OEM schedules will want it),
        // but pull out the handful the UI actually shows.
        $raw = array_filter($result, fn (mixed $value): bool => is_string($value) && $value !== '');

        return [
            'specs' => array_filter([
                'year' => $this->field($raw, 'ModelYear'),
                'make' => $this->titleCase($this->field($raw, 'Make')),
                'model' => $this->field($raw, 'Model'),
                'trim' => $this->field($raw, 'Trim'),
                'engine' => $this->engineFrom($raw),
            ]),
            'raw' => $raw,
        ];
    }

    /**
     * Open recall campaigns for a VIN.
     *
     * @return array<int, array<string, mixed>>
     */
    public function recallsForVin(string $vin): array
    {
        $response = $this->get(
            Config::string('services.nhtsa.recalls_url').'/recalls/recallsByVehicle',
            ['vin' => $vin],
        );

        $results = $response['results'] ?? [];

        return is_array($results) ? array_values(array_filter($results, is_array(...))) : [];
    }

    /**
     * One place for the timeout, retry and failure policy.
     *
     * @param  array<string, string>  $query
     * @return array<string, mixed>|null
     */
    private function get(string $url, array $query): ?array
    {
        try {
            $response = Http::timeout(Config::integer('services.nhtsa.timeout'))
                ->connectTimeout(5)
                ->retry(2, 250, throw: false)
                ->acceptJson()
                ->get($url, $query);

            if ($response->failed()) {
                Log::info('NHTSA request failed', ['url' => $url, 'status' => $response->status()]);

                return null;
            }

            $json = $response->json();

            return is_array($json) ? $json : null;
        } catch (Throwable $e) {
            // Swallowed on purpose: enrichment must never break the app.
            Log::info('NHTSA request threw', ['url' => $url, 'message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function field(array $raw, string $key): ?string
    {
        $value = $raw[$key] ?? null;

        return is_string($value) && $value !== '' ? trim($value) : null;
    }

    /**
     * vPIC spreads the engine across several fields; assemble something a
     * person would recognise, e.g. "2.4L 4cyl 182hp".
     *
     * @param  array<string, mixed>  $raw
     */
    private function engineFrom(array $raw): ?string
    {
        $parts = array_filter([
            ($litres = $this->field($raw, 'DisplacementL')) ? round((float) $litres, 1).'L' : null,
            ($cylinders = $this->field($raw, 'EngineCylinders')) ? $cylinders.'cyl' : null,
            ($hp = $this->field($raw, 'EngineHP')) ? round((float) $hp).'hp' : null,
        ]);

        return $parts === [] ? null : implode(' ', $parts);
    }

    /**
     * vPIC shouts makes in capitals ("SUBARU"), which looks wrong next to a
     * hand-typed model.
     */
    private function titleCase(?string $value): ?string
    {
        return $value === null ? null : str($value)->lower()->title()->value();
    }
}
