<?php

namespace App\Console\Commands;

use App\Actions\Reminders\SendDueReminders;
use App\Models\VehicleInterval;
use Illuminate\Console\Command;

class SendRemindersCommand extends Command
{
    protected $signature = 'reminders:send {--force : Clear the dedupe state and re-send}';

    protected $description = 'Notify owners about services that are due or overdue';

    public function handle(SendDueReminders $reminders): int
    {
        if ($this->option('force')) {
            // Testing aid only. Clears what we have already told the user so
            // the same reminders go out again; the scheduler never uses this.
            VehicleInterval::query()->update([
                'last_reminded_status' => null,
                'last_reminded_at' => null,
            ]);

            $this->comment('Dedupe state cleared (--force).');
        }

        $sent = $reminders->handle();

        $this->info($sent === 0
            ? 'Nothing due — no reminders sent.'
            : "Sent {$sent} reminder(s).");

        return self::SUCCESS;
    }
}
