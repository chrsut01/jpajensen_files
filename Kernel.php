<?php

declare(strict_types=1);

namespace App\Console;

use App\Jobs\ConvertedTjekIndToAttendance;
use Feature;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('telescope:prune --hours=48')->daily();
        $schedule->job(new ConvertedTjekIndToAttendance)->everyFiveMinutes();
        $schedule->command('app:sync-customers')->everyFiveMinutes();
        $schedule->command('app:sync-products')->everyFiveMinutes();
        $schedule->model(WeatherAlertRecord::class)->prune()->daily();
        if (Feature::value('expenses')) {

            $schedule->command('app:queue-expense-processing')->daily();
        }

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $paths = [
            __DIR__.'/Commands',
        ];
        if (app()->isLocal()) {
            $paths[] = __DIR__.'/DevCommands';
        }
        $this->load($paths);

        require base_path('routes/console.php');
    }
}
