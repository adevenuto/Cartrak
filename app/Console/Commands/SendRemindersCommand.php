<?php

namespace App\Console\Commands;

use App\Actions\Reminders\SendDueReminders;
use Illuminate\Console\Command;

class SendRemindersCommand extends Command
{
    protected $signature = 'reminders:send';

    protected $description = 'Notify owners about services that are due or overdue';

    public function handle(SendDueReminders $reminders): int
    {
        $sent = $reminders->handle();

        $this->info($sent === 0
            ? 'Nothing due — no reminders sent.'
            : "Sent {$sent} reminder(s).");

        return self::SUCCESS;
    }
}
