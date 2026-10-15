<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneActivityLog extends Command
{
    protected $signature = 'activity:prune {--days=90 : Delete tracking data older than this many days}';

    protected $description = 'Delete old visitor session and user activity tracking rows';

    public function handle()
    {
        $days = max(7, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $activities = 0;
        do {
            $deleted = DB::table('user_activities')->where('created_at', '<', $cutoff)->limit(5000)->delete();
            $activities += $deleted;
        } while ($deleted > 0);

        $sessions = 0;
        do {
            $deleted = DB::table('visitor_sessions')->where('last_activity_at', '<', $cutoff)->limit(5000)->delete();
            $sessions += $deleted;
        } while ($deleted > 0);

        $this->info("Pruned {$activities} activity rows and {$sessions} session rows older than {$days} days.");

        return Command::SUCCESS;
    }
}
