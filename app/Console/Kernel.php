<?php

namespace App\Console;

use App\Console\Commands\Init;
use App\Console\Commands\Sync;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel
 * @package App\Console
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Init::class,
        Sync::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        /*
         * Sync FoodSoft's database with ours daily.
         */
        $this->FoodSoftImport($schedule);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    /**
     * @param Schedule $schedule
     */
    private function FoodSoftImport(Schedule $schedule): void
    {
        $schedule->command('fo:sync')
            ->dailyAt('23:55')
            ->sendOutputTo(storage_path('schedule-logs'))
            ->onFailure(static function() {
                logger()->error('FoodOrdering:Sync Failed.');
            });
    }
}
