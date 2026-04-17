<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\AutoPublishBlog;
use App\Models\AutoPublishSchedule;
use App\Services\BlogGeneratorService;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class AutoPublishController extends Controller
{
    public function __construct(private readonly BlogGeneratorService $generator) {}

    public function index(): View
    {
        $schedules = AutoPublishSchedule::withCount('blogs')
            ->latest()
            ->paginate(15);

        return view('admin.auto-publish.index', compact('schedules'));
    }

    public function create(): View
    {
        return view('admin.auto-publish.create', [
            'models' => $this->availableModels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSchedule($request);

        AutoPublishSchedule::create($data);

        return redirect()
            ->route('admin.auto-publish.index')
            ->with('success', 'Schedule created. Blogs will begin publishing on the start date.');
    }

    public function show(AutoPublishSchedule $autoPublish): View
    {
        $blogs = $autoPublish->blogs()
            ->latest('published_at')
            ->paginate(10);

        return view('admin.auto-publish.show', compact('autoPublish', 'blogs'));
    }

    public function edit(AutoPublishSchedule $autoPublish): View
    {
        return view('admin.auto-publish.edit', [
            'schedule' => $autoPublish,
            'models'   => $this->availableModels(),
        ]);
    }

    public function update(Request $request, AutoPublishSchedule $autoPublish): RedirectResponse
    {
        $data = $this->validateSchedule($request, isUpdate: true);
        $autoPublish->update($data);

        return redirect()
            ->route('admin.auto-publish.show', $autoPublish)
            ->with('success', 'Schedule updated.');
    }

    public function destroy(AutoPublishSchedule $autoPublish): RedirectResponse
    {
        $autoPublish->delete();

        return redirect()
            ->route('admin.auto-publish.index')
            ->with('success', 'Schedule removed.');
    }

    public function toggleStatus(AutoPublishSchedule $autoPublish): RedirectResponse
    {
        $newStatus = match ($autoPublish->status) {
            'active'  => 'paused',
            'paused'  => 'active',
            'failed'  => 'active',
            default   => 'active',
        };

        $autoPublish->update(['status' => $newStatus, 'last_error' => null]);

        return redirect()->back()->with('success', "Schedule is now: {$newStatus}.");
    }

    /**
     * Dispatch a background job — returns immediately to the browser.
     * The queue worker handles generation and DB updates asynchronously.
     */
    public function triggerNow(AutoPublishSchedule $autoPublish): RedirectResponse
    {
        // ->onQueue('blogs')
        AutoPublishBlog::dispatch($autoPublish);

        return redirect()
            ->route('admin.auto-publish.show', $autoPublish)
            ->with('success', '⚡ Blog generation queued! Post will appear once the worker processes it.');
    }


    // ── Helpers ───────────────────────────────────────────────────────────

    private function validateSchedule(Request $request, bool $isUpdate = false): array
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'context'       => 'required|string|min:20|max:3000',
            'category'      => 'nullable|string|max:100',
            'tags'          => 'nullable|string|max:500',
            'author'        => 'nullable|string|max:100',
            'author_role'   => 'nullable|string|max:100',
            'start_date'    => $isUpdate ? 'required|date' : 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after:start_date',
            'blogs_per_day' => 'required|integer|min:1|max:5',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $validated['tags'] = $this->parseTags($request->input('tags'));

        return $validated;
    }


    private function parseTags(?string $input): ?array
    {
        if (blank($input)) return null;

        return collect(explode(',', $input))
            ->map(fn($t) => trim($t))
            ->filter()
            ->values()
            ->toArray();
    }

    private function availableModels(): array
    {
        return [
            'openrouter' => [
                'anthropic/claude-3.5-sonnet'        => 'Claude 3.5 Sonnet',
                'anthropic/claude-3-opus'             => 'Claude 3 Opus',
                'openai/gpt-4o'                      => 'GPT-4o (via OpenRouter)',
                'openai/gpt-4-turbo'                 => 'GPT-4 Turbo (via OpenRouter)',
                'google/gemini-pro-1.5'              => 'Gemini Pro 1.5',
                'meta-llama/llama-3.1-70b-instruct'  => 'Llama 3.1 70B (Budget)',
            ],
            'claude' => [
                'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
                'claude-3-opus-20240229'     => 'Claude 3 Opus',
                'claude-3-haiku-20240307'    => 'Claude 3 Haiku (Fastest)',
            ],
            'openai' => [
                'gpt-4o'                => 'GPT-4o (Recommended)',
                'gpt-4o-mini'          => 'GPT-4o Mini (Fast & Cheap)',
                'gpt-4-turbo'          => 'GPT-4 Turbo',
                'gpt-4'                => 'GPT-4',
                'gpt-3.5-turbo'        => 'GPT-3.5 Turbo (Budget)',
            ],
        ];
    }
}
