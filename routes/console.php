<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// The provisioning cron pipes `schedule:run` to /dev/null, so each task
// appends its own output to a logfile to keep scheduled-task failures visible.
$scheduleLog = storage_path('logs/schedule.log');

Schedule::command('backup:database')->daily()->appendOutputTo($scheduleLog);
Schedule::command('og:prune-cache')->weekly()->appendOutputTo($scheduleLog);
Schedule::command('page-views:prune')->daily()->appendOutputTo($scheduleLog);
