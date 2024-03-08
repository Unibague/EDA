<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {      //02:00 significa 8:00 am en el horario de aquí en el mundo real
        $schedule->command('commitmentReminder:send')->dailyAt('04:00');
        $schedule->command('aggregate_grades:update')->everyThirtyMinutes();
/*        $schedule->command('assessmentReminder:send')->everyMinute();*/
//        $schedule->command('assessmentReminder:send')->cron('48 20 8 3 *');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
