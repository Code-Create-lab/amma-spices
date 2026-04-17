<?php

return [

    'default'      => env('AI_PROVIDER', 'openrouter'),

    // Primary model — openrouter/free auto-picks any live free model
    'active_model' => env('AI_MODEL', 'openrouter/free'),

    // Fallback chain — tried in order if primary fails with 404/503
    'fallback_models' => array_filter([
        env('AI_MODEL_FALLBACK',   'meta-llama/llama-3.3-70b-instruct:free'),
        env('AI_MODEL_FALLBACK_2', 'mistralai/mistral-small-3.1-24b-instruct:free'),
    ]),

    'providers' => [

        'openrouter' => [
            'api_key'  => env('OPENROUTER_API_KEY'),
            'base_url' => 'https://openrouter.ai/api/v1',
            'site_url' => env('APP_URL', 'http://localhost'),
            'site_name' => env('APP_NAME', 'My App'),
        ],

        'claude' => [
            'api_key'  => env('CLAUDE_API_KEY'),
            'base_url' => 'https://api.anthropic.com',
            'version'  => '2023-06-01',
        ],

        'openai' => [
            'api_key'      => env('OPENAI_API_KEY'),
            'base_url'     => 'https://api.openai.com/v1',
            'organization' => env('OPENAI_ORGANIZATION'),
        ],
        'groq' => [
            'api_key'       => env('GROQ_API_KEY'),
            'base_url'      => 'https://api.groq.com/openai/v1',  // OpenAI-compatible
            'default_model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
            'fallback_models' => array_filter([
                env('GROQ_MODEL_FALLBACK',   'llama3-70b-8192'),
                env('GROQ_MODEL_FALLBACK_2', 'mixtral-8x7b-32768'),
            ]),
        ],

    ],

    'blog_generation' => [
        'max_tokens'  => (int)   env('AI_BLOG_MAX_TOKENS',  4000),
        'temperature' => (float) env('AI_BLOG_TEMPERATURE', 0.7),
    ],

];
