<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule as IlluminateSchedule;
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

// flare-client registers its own flush task, and a package cannot know about this
// app's logging convention. Applying it to every registered event rather than only
// to the three above keeps the rule true as packages come and go.
foreach (app(IlluminateSchedule::class)->events() as $event) {
    $event->appendOutputTo($scheduleLog);
}
