<?php

namespace App\Console;

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
        \App\Console\Commands\GenerateScheduledBlogPosts::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->command('orders:check-pending')->everyMinute();


        $schedule->command('payments:check-pending --limit=100 --days=7')
            ->everyMinute()
            ->withoutOverlapping()    // Prevent concurrent runs
            ->onOneServer()            // Only run on one server if clustered
            ->runInBackground();       // Don't block other scheduled tasks

        $schedule->command('blogs:generate-scheduled')->dailyAt('08:00');
        $schedule->command('orders:mark-failed --minutes=30 --limit=100')
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();

        $schedule->command('refunds:process')->hourly();

        // Keep activity tracking tables from growing unbounded
        $schedule->command('activity:prune --days=90')->dailyAt('03:30');

        // Rebuild sitemap.xml nightly so new products, categories and posts get
        // indexed without anyone remembering to regenerate it. Uses APP_URL, so
        // that must be the public https:// address in production.
        $schedule->command('sitemap:generate')
            ->dailyAt('04:15')
            ->onOneServer();

        // Retry failed jobs twice a week
        $schedule->command('queue:retry all')
            ->weeklyOn(2, '02:00'); // Tuesday 2 AM

        $schedule->command('queue:retry all')
            ->weeklyOn(5, '02:00'); // Friday 2 AM
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
