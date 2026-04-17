<?php

namespace App\Services;

use App\Models\AutoPublishSchedule;
use App\Models\Blog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BlogGeneratorService
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
You are an expert blog writer, content strategist, and SEO specialist.

CRITICAL RULES:
1. Respond with ONLY a valid JSON object — no markdown, no code fences, no preamble.
2. The "content" field must be clean HTML using ONLY: <h2>, <h3>, <p>, <ul>, <ol>, <li>, <blockquote>, <strong>, <em>.
3. Do NOT include <html>, <body>, <head>, or <h1> tags in content. The title is rendered separately.
4. Content must be at least 900 words (strip_tags word count).

Required JSON structure (exact keys, no extras):
{
  "title":            "Compelling SEO title, 55–70 characters",
  "excerpt":          "2–3 sentence hook for listing pages, 150–200 characters",
  "content":          "<h2>Introduction</h2><p>...</p>...",
  "tags":             ["tag1", "tag2", "tag3", "tag4", "tag5"],
  "read_time":        5,
  "meta_title":       "SEO meta title, 50–60 characters",
  "meta_description": "SEO meta description, 150–160 characters"
}

Content structure:
- Engaging intro paragraph (no heading)
- 4–5 <h2> sections with body paragraphs
- At least one <blockquote> with a key insight
- At least one <ul> or <ol> list
- A "Conclusion" or "Key Takeaways" section at the end
PROMPT;

    // ── Public API ────────────────────────────────────────────────────────

    /**
     * Provider and model come from config(), not from the schedule.
     */
    public function generateAndStore(
        AutoPublishSchedule $schedule,
        string $provider,
        string $model
    ): Blog {
        $this->validateProviderKey($provider);

        $prompt  = $this->buildUserPrompt($schedule);

        $rawJson = match ($provider) {
            'openrouter' => $this->callOpenRouter($prompt, $model),
            'claude'     => $this->callClaude($prompt, $model),
            'openai'     => $this->callOpenAI($prompt, $model),
            'groq'       => $this->callGroq($prompt, $model),
            default      => throw new \RuntimeException("Unknown AI provider: [{$provider}]"),
        };

        $data = $this->parseAndValidateJson($rawJson);

        return $this->persistBlog($data, $schedule);
    }

    // ── Prompt Construction ───────────────────────────────────────────────

    private function buildUserPrompt(AutoPublishSchedule $schedule): string
    {
        $lines = [
            'Generate a complete, high-quality blog article with the following parameters:',
            '',
            "TOPIC / CONTEXT:\n{$schedule->context}",
        ];

        if ($schedule->category) {
            $lines[] = "CATEGORY: {$schedule->category}";
        }

        if (! empty($schedule->tags)) {
            $lines[] = 'SUGGESTED TAGS (use some, or generate relevant ones): '
                . implode(', ', $schedule->tags);
        }

        if ($schedule->author) {
            $byline = $schedule->author_role
                ? "{$schedule->author}, {$schedule->author_role}"
                : $schedule->author;
            $lines[] = "AUTHOR VOICE: Write as an article authored by {$byline}.";
        }

        $published = Blog::where('schedule_id', $schedule->id)
            ->latest('published_at')
            ->limit(14)
            ->pluck('title')
            ->toArray();

        if (! empty($published)) {
            $lines[] = '';
            $lines[] = 'ALREADY PUBLISHED in this series — pick a DIFFERENT angle:';
            foreach ($published as $t) {
                $lines[] = "  - {$t}";
            }
        }

        $lines[] = '';
        $lines[] = "Today's date: " . now()->format('F j, Y') . '. Make it feel timely and current.';

        return implode("\n", $lines);
    }

    // ── API Callers ───────────────────────────────────────────────────────

    private function callOpenRouter(string $prompt, string $model): string
    {
        $cfg = config('ai.providers.openrouter');

        // Build the full model queue: primary first, then fallbacks
        $modelQueue = array_unique(array_filter(array_merge(
            [$model],
            config('ai.fallback_models', [])
        )));

        $lastError = null;

        foreach ($modelQueue as $attempt => $currentModel) {

            if ($attempt > 0) {
                Log::warning('OpenRouter: trying fallback model', [
                    'failed_model'   => $modelQueue[$attempt - 1],
                    'fallback_model' => $currentModel,
                    'reason'         => $lastError,
                ]);
            }

            try {
                $response = Http::timeout(300)
                    ->withHeaders([
                        'Authorization' => "Bearer {$cfg['api_key']}",
                        'HTTP-Referer'  => $cfg['site_url'],
                        'X-Title'       => $cfg['site_name'],
                    ])
                    ->post("{$cfg['base_url']}/chat/completions", [
                        'model'       => $currentModel,
                        'messages'    => [
                            ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                            ['role' => 'user',   'content' => $prompt],
                        ],
                        'max_tokens'  => config('ai.blog_generation.max_tokens'),
                        'temperature' => config('ai.blog_generation.temperature'),
                    ]);

                // 404 = model retired/unavailable → try next in chain
                // 429 = rate limited              → try next in chain
                // 503 = model overloaded          → try next in chain
                if (in_array($response->status(), [404, 429, 503])) {
                    $lastError = "HTTP {$response->status()}: " . $response->json('error.message', $response->body());
                    continue;
                }

                if ($response->failed()) {
                    throw new \RuntimeException(
                        "OpenRouter [{$response->status()}]: " . $response->body()
                    );
                }

                $content = $response->json('choices.0.message.content');

                if (empty($content)) {
                    $lastError = 'Empty content returned';
                    continue;
                }

                Log::info('OpenRouter: model used successfully', ['model' => $currentModel]);

                return $content;
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // Timeout — move to next model immediately, don't retry the same slow model
                $lastError = 'Connection timeout: ' . $e->getMessage();
                Log::warning('OpenRouter: timeout, trying next model', [
                    'model' => $currentModel,
                    'error' => $lastError,
                ]);
                continue;
            }
        }

        // All models in the chain failed
        throw new \RuntimeException(
            "All OpenRouter models failed. Last error: {$lastError}. " .
                "Check https://openrouter.ai/models for currently available free models."
        );
    }

    private function callGroq(string $prompt, string $model): string
    {
        $cfg = config('ai.providers.groq');

        // Primary model first, then Groq-specific fallbacks
        $modelQueue = array_unique(array_filter(array_merge(
            [$model],
            $cfg['fallback_models'] ?? []
        )));

        $lastError = null;

        foreach ($modelQueue as $attempt => $currentModel) {

            if ($attempt > 0) {
                Log::warning('Groq: trying fallback model', [
                    'failed_model'   => $modelQueue[$attempt - 1],
                    'fallback_model' => $currentModel,
                    'reason'         => $lastError,
                ]);
            }

            try {
                $response = Http::timeout(300)
                    ->withHeaders([
                        'Authorization' => "Bearer {$cfg['api_key']}",
                    ])
                    ->post("{$cfg['base_url']}/chat/completions", [
                        'model'       => $currentModel,
                        'messages'    => [
                            ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                            ['role' => 'user',   'content' => $prompt],
                        ],
                        'max_tokens'  => config('ai.blog_generation.max_tokens'),
                        'temperature' => config('ai.blog_generation.temperature'),
                        // Groq supports JSON mode natively — forces valid JSON output
                        'response_format' => ['type' => 'json_object'],
                    ]);

                // 404 = model removed from Groq
                // 429 = rate limit hit (Groq free tier is strict: ~30 req/min)
                // 503 = model temporarily overloaded
                if (in_array($response->status(), [404, 429, 503])) {
                    $lastError = "HTTP {$response->status()}: " . $response->json('error.message', $response->body());
                    Log::warning('Groq: skipping model', [
                        'model'  => $currentModel,
                        'status' => $response->status(),
                        'error'  => $lastError,
                    ]);
                    continue;
                }

                if ($response->failed()) {
                    throw new \RuntimeException(
                        "Groq [{$response->status()}]: " . $response->body()
                    );
                }

                $content = $response->json('choices.0.message.content');

                if (empty($content)) {
                    $lastError = 'Empty content returned';
                    continue;
                }

                Log::info('Groq: model used successfully', ['model' => $currentModel]);

                return $content;
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // Groq is normally very fast — a timeout here usually means the
                // model is overloaded, so move to the next one immediately.
                $lastError = 'Connection timeout: ' . $e->getMessage();
                Log::warning('Groq: timeout, trying next model', [
                    'model' => $currentModel,
                    'error' => $lastError,
                ]);
                continue;
            }
        }

        throw new \RuntimeException(
            "All Groq models failed. Last error: {$lastError}. " .
                "Check https://console.groq.com for currently available models."
        );
    }
    private function callClaude(string $prompt, string $model): string
    {
        $cfg = config('ai.providers.claude');

        $response = Http::timeout(120)
            ->retry(2, 3000, throw: false)
            ->withHeaders([
                'x-api-key'         => $cfg['api_key'],
                'anthropic-version' => $cfg['version'],
            ])
            ->post("{$cfg['base_url']}/v1/messages", [
                'model'      => $model,
                'max_tokens' => config('ai.blog_generation.max_tokens'),
                'system'     => self::SYSTEM_PROMPT,
                'messages'   => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException("Claude [{$response->status()}]: " . $response->body());
        }

        $block = collect($response->json('content'))->firstWhere('type', 'text');

        if (empty($block['text'])) {
            throw new \RuntimeException('Claude returned no text block in response.');
        }

        return $block['text'];
    }

    private function callOpenAI(string $prompt, string $model): string
    {
        $cfg = config('ai.providers.openai');

        $headers = ['Authorization' => "Bearer {$cfg['api_key']}"];

        if (! empty($cfg['organization'])) {
            $headers['OpenAI-Organization'] = $cfg['organization'];
        }

        $response = Http::timeout(120)
            ->retry(2, 3000, throw: false)
            ->withHeaders($headers)
            ->post("{$cfg['base_url']}/chat/completions", [
                'model'           => $model,
                'messages'        => [
                    ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                    ['role' => 'user',   'content' => $prompt],
                ],
                'max_tokens'      => config('ai.blog_generation.max_tokens'),
                'temperature'     => config('ai.blog_generation.temperature'),
                'response_format' => ['type' => 'json_object'],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException("OpenAI [{$response->status()}]: " . $response->body());
        }

        return $this->extractOpenRouterContent($response->json());
    }

    // ── JSON Parsing ──────────────────────────────────────────────────────

    private function parseAndValidateJson(string $raw): array
    {
        $clean = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $clean = preg_replace('/\s*```\s*$/m', '', $clean);
        $clean = trim($clean);

        if (! str_starts_with($clean, '{')) {
            preg_match('/\{.*\}/s', $clean, $m);
            $clean = $m[0] ?? $clean;
        }

        $data = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                'JSON parse error: ' . json_last_error_msg() .
                    ' | Raw (first 600 chars): ' . substr($clean, 0, 600)
            );
        }

        foreach (['title', 'excerpt', 'content'] as $key) {
            if (empty($data[$key])) {
                throw new \RuntimeException("AI response missing required field: [{$key}]");
            }
        }

        return $data;
    }

    // ── Persistence ───────────────────────────────────────────────────────

    private function persistBlog(array $data, AutoPublishSchedule $schedule): Blog
    {
        $title = trim($data['title']);
        $slug  = $this->uniqueSlug($title);

        return Blog::create([
            'schedule_id'      => $schedule->id,
            'title'            => $title,
            'slug'             => $slug,
            'thumbnail'        => $this->thumbnailUrl($slug),
            'category'         => $schedule->category,
            'author'           => $schedule->author,
            'author_role'      => $schedule->author_role,
            'excerpt'          => $data['excerpt']          ?? null,
            'content'          => $data['content'],
            'read_time'        => $data['read_time']        ?? $this->estimateReadTime($data['content']),
            'tags'             => $data['tags']             ?? $schedule->tags,
            'status'           => 'published',
            'published_at'     => now(),
            'meta_title'       => $data['meta_title']       ?? Str::limit($title, 60),
            'meta_description' => $data['meta_description'] ?? Str::limit(strip_tags($data['excerpt'] ?? ''), 160),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function extractOpenRouterContent(array $json): string
    {
        $content = data_get($json, 'choices.0.message.content');

        if (empty($content)) {
            throw new \RuntimeException('API returned an empty content field.');
        }

        return $content;
    }

    private function uniqueSlug(string $title): string
    {
        $base  = Str::slug($title);
        $slug  = $base;
        $count = 1;

        while (Blog::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }

    private function estimateReadTime(string $html): int
    {
        return (int) max(1, round(str_word_count(strip_tags($html)) / 200));
    }

    private function thumbnailUrl(string $slug): string
    {
        return 'https://picsum.photos/seed/' . substr(md5($slug), 0, 10) . '/1200/630';
    }

    private function validateProviderKey(string $provider): void
    {
        $key = config("ai.providers.{$provider}.api_key");

        if (empty($key)) {
            throw new \RuntimeException(
                "API key for provider [{$provider}] is missing. Set it in your .env file."
            );
        }
    }
}
