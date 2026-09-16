<?php

namespace App\Console\Commands;

use App\Actions\Events\LogEvent;
use App\Actions\Vehicles\SeedVehicleIntervals;
use App\Enums\EventType;
use App\Models\Recall;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\VehicleMileage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

/**
 * Builds a demo vehicle whose gauges sit in every state at once.
 *
 * Exercising the widgets by hand means editing intervals one at a time and
 * waiting for the arithmetic to land somewhere useful. This puts one of each on
 * screen immediately: healthy, soon, due, overdue and uncalibrated, plus enough
 * fuel history for MPG and the EPA benchmark, and an open recall.
 *
 * Re-running replaces the demo vehicle, so it is safe to call repeatedly and it
 * never touches a real one.
 */
class DemoGarageCommand extends Command
{
    protected $signature = 'demo:garage {--email= : Which user to attach it to}
                            {--clean : Remove the demo vehicle and stop}';

    protected $description = 'Create a demo vehicle with gauges in every state';

    private const NICKNAME = 'Demo Car';

    public function handle(SeedVehicleIntervals $seed, LogEvent $log): int
    {
        $user = $this->resolveUser();

        if ($user === null) {
            $this->error('No user found. Register first, or pass --email.');

            return self::FAILURE;
        }

        // Per-row so the observers fire and photo files are cleaned up.
        $user->vehicles()->where('nickname', self::NICKNAME)->get()
            ->each(fn (Vehicle $vehicle) => $vehicle->delete());

        if ($this->option('clean')) {
            $this->info('Demo vehicle removed.');

            return self::SUCCESS;
        }

        $vehicle = $user->vehicles()->create([
            'nickname' => self::NICKNAME,
            'year' => 2019,
            'make' => 'Toyota',
            'model' => 'RAV4',
            'trim' => 'XLE',
            'color' => '#1f4e9c',
            'epa_mpg_city' => 26,
            'epa_mpg_highway' => 33,
            'epa_mpg_combined' => 29,
        ]);

        $seed->handle($vehicle);
        $this->seedReadings($vehicle, $log);
        $this->seedGaugeStates($vehicle);
        $this->seedRecall($vehicle);

        $this->info("Created \"{$vehicle->displayName()}\" for {$user->email}.");
        $this->line('  gauges: one each of healthy / soon / due / overdue, rest uncalibrated');
        $this->line('  fuel:   4 full tanks, so MPG and the EPA benchmark render');
        $this->line('  recall: 1 open campaign');
        $this->newLine();
        $this->line('  php artisan reminders:send --force   # see the emails');
        $this->line('  php artisan demo:garage --clean      # remove it again');

        return self::SUCCESS;
    }

    /**
     * Four full tanks roughly 300 miles apart, so MPG is computed between them
     * and the daily rate has a real window to average over.
     */
    private function seedReadings(Vehicle $vehicle, LogEvent $log): void
    {
        $odometer = 52_000;

        foreach ([90, 60, 30, 3] as $daysAgo) {
            $log->handle($vehicle, [
                'type' => EventType::Fuel,
                'odometer' => $odometer,
                'occurred_on' => Date::today()->subDays($daysAgo)->toDateString(),
                'gallons' => 11.2,
                'full_tank' => true,
                'cost_cents' => 4285,
            ]);

            $odometer += 310;
        }
    }

    /**
     * One interval deliberately placed in each status, so every ring colour and
     * every label variant is on screen together.
     */
    private function seedGaugeStates(Vehicle $vehicle): void
    {
        // Offsets are measured from the PROJECTED odometer, because that is what
        // the gauges read — measuring from the last recorded value would leave
        // every one of these a few hundred miles off target.
        $projected = VehicleMileage::for($vehicle->fresh())->projectedOdometer;

        $states = [
            // key                months ago   miles driven since last done
            'oil-change' => [5, 5_750],        // overdue on mileage (5,000 interval)
            'registration' => [14, null],      // overdue on time (12 month, time-only)
            'brake-pads' => [40, 40_000],      // due on mileage, exactly at the line
            'tire-rotation' => [5, 900],       // soon on time (6 month interval)
            'cabin-air-filter' => [1, 600],    // healthy on both
        ];

        foreach ($states as $key => [$monthsAgo, $milesDriven]) {
            $type = ServiceType::where('key', $key)->first();

            if ($type === null) {
                continue;
            }

            $vehicle->intervals()->where('service_type_id', $type->id)->update([
                'last_done_at' => Date::today()->subMonths($monthsAgo)->toDateString(),
                'last_done_odometer' => $milesDriven === null ? null : $projected - $milesDriven,
                'last_reminded_status' => null,
                'last_reminded_at' => null,
            ]);
        }
    }

    private function seedRecall(Vehicle $vehicle): void
    {
        Recall::factory()->for($vehicle)->create([
            'campaign_number' => '19V123000',
            'component' => 'AIR BAGS:FRONTAL:DRIVER SIDE INFLATOR MODULE',
            'summary' => 'The driver frontal air bag inflator may rupture during deployment.',
            'remedy' => 'Dealers will replace the driver frontal air bag inflator, free of charge.',
            'reported_on' => Date::today()->subMonths(8)->toDateString(),
        ]);
    }

    private function resolveUser(): ?User
    {
        $email = $this->option('email');

        return is_string($email) && $email !== ''
            ? User::where('email', $email)->first()
            : User::orderBy('id')->first();
    }
}
