<?php

namespace App\Console\Commands;

use App\Models\AutoPublishSchedule;
use App\Services\BlogGeneratorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateScheduledBlogPosts extends Command
{
    protected $signature = 'blogs:generate-scheduled
                            {--id=    : Target a specific schedule ID}
                            {--dry-run : Simulate without writing to DB}';

    protected $description = 'Generate and publish AI blog posts for all active schedules due today.';

    public function __construct(private readonly BlogGeneratorService $generator)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('[' . now()->toDateTimeString() . '] Checking scheduled blog generation...');

        $schedules = $this->resolveSchedules();

        if ($schedules->isEmpty()) {
            $this->info('No active schedules are due today.');
            return self::SUCCESS;
        }

        $generated = 0;
        $failed    = 0;

        foreach ($schedules as $schedule) {
            $this->line('');
            $this->line("▶ Schedule [{$schedule->id}]: {$schedule->name}");
            $this->line("  Provider : {$schedule->ai_provider} / {$schedule->ai_model}");
            $this->line("  Context  : " . str($schedule->context)->limit(80));

            for ($attempt = 1; $attempt <= $schedule->blogs_per_day; $attempt++) {
                $this->line("  Generating post {$attempt}/{$schedule->blogs_per_day}...");

                if ($this->option('dry-run')) {
                    $this->warn("  [DRY RUN] Skipping actual generation.");
                    continue;
                }

                try {
                    $blog = $this->generator->generateAndStore($schedule);

                    $schedule->increment('total_generated');
                    $schedule->update([
                        'last_run_at' => now(),
                        'last_error'  => null,
                    ]);

                    $this->info("  ✓ Published: \"{$blog->title}\" (ID: {$blog->id})");
                    $generated++;

                    Log::info('Auto-blog published', [
                        'schedule_id' => $schedule->id,
                        'blog_id'     => $blog->id,
                        'title'       => $blog->title,
                    ]);

                } catch (\Throwable $e) {
                    $failed++;

                    $schedule->update([
                        'last_error' => $e->getMessage(),
                        'status'     => 'failed',
                    ]);

                    $this->error("  ✗ Failed: " . $e->getMessage());

                    Log::error('Auto-blog generation failed', [
                        'schedule_id' => $schedule->id,
                        'error'       => $e->getMessage(),
                    ]);
                }
            }

            // Auto-complete when the end date window closes after today's run
            if ($schedule->end_date->lte(today())) {
                $schedule->update(['status' => 'completed']);
                $this->line("  ✓ Schedule marked COMPLETED (end date reached).");
            }
        }

        $this->line('');
        $this->info("Finished. Generated: {$generated} | Failed: {$failed}");

        return ($failed > 0) ? self::FAILURE : self::SUCCESS;
    }

    private function resolveSchedules()
    {
        $query = AutoPublishSchedule::query()
            ->where('status', 'active')
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date',   '>=', today())
            ->where(function ($q) {
                $q->whereNull('last_run_at')
                  ->orWhereDate('last_run_at', '<', today());
            });

        if ($id = $this->option('id')) {
            $query->where('id', $id);
        }

        return $query->get();
    }
}