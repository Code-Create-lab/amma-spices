<?php

namespace App\Jobs;

use App\Models\AutoPublishSchedule;
use App\Services\BlogGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoPublishBlog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $backoff = 60;
    public int $timeout = 360;

    public function __construct(
        public readonly AutoPublishSchedule $schedule
    ) {}

    public function handle(BlogGeneratorService $generator): void
    {
        // Always read from config — never from the schedule row
        $provider = config('ai.default');
        $model    = config('ai.active_model');

        Log::info('AutoPublishBlog job started', [
            'schedule_id' => $this->schedule->id,
            'provider'    => $provider,
            'model'       => $model,
        ]);

        try {
            $blog = $generator->generateAndStore($this->schedule, $provider, $model);

            $this->schedule->increment('total_generated');
            $this->schedule->update([
                'last_run_at' => now(),
                'last_error'  => null,
                'status'      => $this->schedule->status === 'failed'
                    ? 'active'
                    : $this->schedule->status,
            ]);

            if ($this->schedule->fresh()->end_date->lte(today())) {
                $this->schedule->update(['status' => 'completed']);
            }

            Log::info('AutoPublishBlog job completed', [
                'schedule_id' => $this->schedule->id,
                'blog_id'     => $blog->id,
                'title'       => $blog->title,
            ]);
        } catch (\Throwable $e) {
            $this->schedule->update([
                'last_error' => $e->getMessage(),
                'status'     => 'failed',
            ]);

            Log::error('AutoPublishBlog job failed', [
                'schedule_id' => $this->schedule->id,
                'attempt'     => $this->attempts(),
                'error'       => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        $this->schedule->update([
            'status'     => 'failed',
            'last_error' => '[Final failure after retries] ' . $e->getMessage(),
        ]);

        Log::error('AutoPublishBlog permanently failed', [
            'schedule_id' => $this->schedule->id,
            'error'       => $e->getMessage(),
        ]);
    }
}
