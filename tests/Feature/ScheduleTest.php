<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    public function test_every_scheduled_task_appends_output_to_the_schedule_log(): void
    {
        // Loads the schedule defined in routes/console.php.
        $this->artisan('schedule:list')->assertExitCode(0);

        $events = app(Schedule::class)->events();
        $expected = storage_path('logs/schedule.log');

        $this->assertNotEmpty($events);

        foreach ($events as $event) {
            $this->assertSame($expected, $event->output, "[{$event->command}] should log to schedule.log");
            $this->assertTrue($event->shouldAppendOutput, "[{$event->command}] should append, not overwrite");
        }
    }
}
