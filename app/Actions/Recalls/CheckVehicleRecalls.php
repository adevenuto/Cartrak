<?php

namespace App\Actions\Recalls;

use App\Models\Recall;
use App\Models\Vehicle;
use App\Notifications\RecallNotification;
use App\Services\Nhtsa\NhtsaClient;
use Illuminate\Support\Carbon;

/**
 * Refreshes a vehicle's cached recalls from NHTSA and notifies on new ones.
 *
 * Campaigns are upserted by NHTSA's own campaign number, so a weekly re-check
 * is idempotent — and only a campaign we have never stored triggers a
 * notification. Without that, every poll would re-announce the same recall.
 *
 * Existing rows are never deleted when the API returns nothing: an outage or a
 * malformed response must not silently erase a safety notice the owner has
 * already been shown.
 */
class CheckVehicleRecalls
{
    public function __construct(private NhtsaClient $nhtsa) {}

    /**
     * @return int the number of newly discovered campaigns
     */
    public function handle(Vehicle $vehicle, bool $notify = true): int
    {
        if (blank($vehicle->vin)) {
            return 0;
        }

        $campaigns = $this->nhtsa->recallsForVin($vehicle->vin);

        if ($campaigns === []) {
            return 0;
        }

        $known = $vehicle->recalls()->pluck('campaign_number')->all();
        $new = 0;

        foreach ($campaigns as $campaign) {
            $number = $this->string($campaign, 'NHTSACampaignNumber');

            if ($number === null) {
                continue;
            }

            $isNew = ! in_array($number, $known, true);

            $recall = $vehicle->recalls()->updateOrCreate(
                ['campaign_number' => $number],
                [
                    'component' => $this->string($campaign, 'Component'),
                    'summary' => $this->string($campaign, 'Summary'),
                    'remedy' => $this->string($campaign, 'Remedy'),
                    'consequence' => $this->string($campaign, 'Consequence'),
                    'reported_on' => $this->date($campaign, 'ReportReceivedDate'),
                ],
            );

            if ($isNew) {
                $new++;

                if ($notify) {
                    $vehicle->user->notify(new RecallNotification($vehicle, $recall));
                }
            }
        }

        return $new;
    }

    /**
     * @param  array<string, mixed>  $campaign
     */
    private function string(array $campaign, string $key): ?string
    {
        $value = $campaign[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    /**
     * @param  array<string, mixed>  $campaign
     */
    private function date(array $campaign, string $key): ?string
    {
        $value = $this->string($campaign, $key);

        if ($value === null) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            // A date we cannot parse is not worth failing a safety notice over.
            return null;
        }
    }
}
