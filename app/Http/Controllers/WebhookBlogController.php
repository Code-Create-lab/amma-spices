<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\ReceivedBlog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookBlogController extends Controller
{
    public function handle(Request $request)
    {
        $event = $request->input('event');

        // $signature = $request->header('X-Webhook-Signature');
        
        // $expected  = hash_hmac('sha256', $request->getContent(), config('services.blog_engine.webhook_secret'));

        // if (!hash_equals($expected, $signature ?? '')) {
        //     return response()->json(['status' => 'unauthorized'], 401);
        // }

        // Handle test pings
        if ($event === 'webhook.test') {
            Log::info('Webhook test received', $request->all());
            return response()->json([
                'status'  => 'ok',
                'message' => 'Test ping received successfully!',
            ]);
        }

        // Handle blog published events
        if ($event === 'blog.published') {
            return $this->handleBlogPublished($request);
        }

        return response()->json([
            'status'  => 'ignored',
            'message' => "Unknown event: {$event}",
        ], 200);
    }

    private function handleBlogPublished(Request $request)
    {
        $blog = $request->input('blog');

        if (empty($blog['id']) || empty($blog['title'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Missing required blog fields',
            ], 422);
        }

        $receivedBlog = Blog::updateOrCreate(
            ['remote_id' => $blog['id']],
            [
                'title'            => $blog['title'],
                'slug'             => $blog['slug'],
                'excerpt'          => $blog['excerpt'] ?? null,
                'content'          => $blog['content'],
                'read_time'        => $blog['read_time'] ?? null,
                'category'         => $blog['category'] ?? null,
                'tags'             => $blog['tags'] ?? null,
                'author_name'      => $blog['author']['name'] ?? null,
                'author_role'      => $blog['author']['role'] ?? null,
                'thumbnail'        => $blog['thumbnail'] ?? null,
                'meta_title'       => $blog['seo']['meta_title'] ?? null,
                'meta_description' => $blog['seo']['meta_description'] ?? null,
                'status'           => $blog['status'] ?? 'published',
                'published_at'     => $blog['published_at'] ?? now(),
            ]
        );

        Log::info('Blog received via webhook', [
            'remote_id' => $receivedBlog->remote_id,
            'title'     => $receivedBlog->title,
        ]);

        return response()->json([
            'status'  => 'ok',
            'message' => 'Blog stored successfully',
            'blog_id' => $receivedBlog->id,
        ]);
    }
}
