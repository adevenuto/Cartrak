<?php

namespace App\Services\Epa;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * EPA fueleconomy.gov — the sticker MPG we benchmark real economy against.
 *
 * Two calls: a menu lookup that turns year/make/model into variant ids, then a
 * detail fetch for the figures. Like NHTSA, this is enrichment only — every
 * path returns null rather than throwing, and the app is fully usable without
 * it.
 *
 * A model usually has several variants (manual vs automatic, engine options).
 * We take the first unless a trim hint matches, because a rough sticker figure
 * is still a useful benchmark and asking the user to pick a transmission to see
 * one number would be a bad trade.
 */
class FuelEconomyClient
{
    /**
     * @return array{city: int, highway: int, combined: int}|null
     */
    public function lookup(int $year, string $make, string $model, ?string $trim = null): ?array
    {
        $options = $this->get('/vehicle/menu/options', [
            'year' => (string) $year,
            'make' => $make,
            'model' => $model,
        ]);

        $items = $options['menuItem'] ?? null;

        if (! is_array($items) || $items === []) {
            return null;
        }

        // A single result comes back as an object rather than a list.
        $items = array_is_list($items) ? $items : [$items];

        $id = $this->pickVariant($items, $trim);

        if ($id === null) {
            return null;
        }

        $detail = $this->get("/vehicle/{$id}", []);

        if ($detail === null) {
            return null;
        }

        $combined = $this->int($detail, 'comb08');

        // Zero means "not rated" in this dataset, which is not a benchmark.
        return $combined === null || $combined <= 0 ? null : [
            'city' => $this->int($detail, 'city08') ?? $combined,
            'highway' => $this->int($detail, 'highway08') ?? $combined,
            'combined' => $combined,
        ];
    }

    /**
     * @param  array<int, mixed>  $items
     */
    private function pickVariant(array $items, ?string $trim): ?string
    {
        $first = null;

        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['value'])) {
                continue;
            }

            $value = (string) $item['value'];
            $first ??= $value;

            $text = is_string($item['text'] ?? null) ? $item['text'] : '';

            if ($trim !== null && $trim !== '' && str_contains(strtolower($text), strtolower($trim))) {
                return $value;
            }
        }

        return $first;
    }

    /**
     * @param  array<string, string>  $query
     * @return array<string, mixed>|null
     */
    private function get(string $path, array $query): ?array
    {
        try {
            $response = Http::timeout(Config::integer('services.fueleconomy.timeout'))
                ->connectTimeout(5)
                ->retry(2, 250, throw: false)
                ->acceptJson()
                ->get(Config::string('services.fueleconomy.url').$path, $query);

            if ($response->failed()) {
                return null;
            }

            $json = $response->json();

            return is_array($json) ? $json : null;
        } catch (Throwable $e) {
            Log::info('EPA request failed', ['path' => $path, 'message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function int(array $data, string $key): ?int
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (int) round((float) $value) : null;
    }
}
