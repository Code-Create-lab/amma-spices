@extends('admin.layout.app')

@section('title', $autoPublish->name)

@section('content')
    <div class="min-h-screen bg-gray-950 text-gray-100 p-6 lg:p-10">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('admin.auto-publish.index') }}" class="hover:text-gray-300 transition-colors">AI
                Auto-Publisher</a>
            <span>/</span>
            <span class="text-gray-300 line-clamp-1 max-w-sm">{{ $autoPublish->name }}</span>
        </nav>

        {{-- Flash --}}
        @if (session('success'))
            <div
                class="flex items-center gap-3 bg-emerald-900/50 border border-emerald-700 text-emerald-300 text-sm px-4 py-3 rounded-lg mb-6">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div
                class="flex items-center gap-3 bg-red-900/50 border border-red-700 text-red-300 text-sm px-4 py-3 rounded-lg mb-6">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Error Alert --}}
        @if ($autoPublish->last_error)
            <div
                class="flex items-start gap-3 bg-red-950/60 border border-red-800 text-red-300 text-sm px-4 py-3 rounded-lg mb-6">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                        clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="font-medium text-red-200 mb-0.5">Last run failed</p>
                    <p class="text-red-400 font-mono text-xs">{{ $autoPublish->last_error }}</p>
                    <form action="{{ route('admin.auto-publish.toggle-status', $autoPublish) }}" method="POST"
                        class="mt-2 inline-block">
                        @csrf
                        <button type="submit" class="text-xs text-red-300 hover:text-white underline">Retry
                            Schedule</button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 mb-8">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-2xl font-bold text-white">{{ $autoPublish->name }}</h1>
                    @php
                        $c = [
                            'active' => 'bg-emerald-500/20 text-emerald-400 border-emerald-700',
                            'paused' => 'bg-yellow-500/20 text-yellow-400 border-yellow-700',
                            'completed' => 'bg-blue-500/20 text-blue-400 border-blue-700',
                            'failed' => 'bg-red-500/20 text-red-400 border-red-700',
                        ];
                        $d = [
                            'active' => 'bg-emerald-400 animate-pulse',
                            'paused' => 'bg-yellow-400',
                            'completed' => 'bg-blue-400',
                            'failed' => 'bg-red-400',
                        ];
                    @endphp
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full border {{ $c[$autoPublish->status] ?? 'bg-gray-700 text-gray-400 border-gray-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $d[$autoPublish->status] ?? 'bg-gray-400' }}"></span>
                        {{ ucfirst($autoPublish->status) }}
                    </span>
                </div>
                <p class="text-gray-400 text-sm">
                    {{ $autoPublish->start_date->format('d M Y') }} → {{ $autoPublish->end_date->format('d M Y') }}
                    · {{ $autoPublish->blogs_per_day }} post{{ $autoPublish->blogs_per_day > 1 ? 's' : '' }} / day
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <form action="{{ route('admin.auto-publish.trigger-now', $autoPublish) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Generate one blog post right now?')"
                        class="inline-flex items-center gap-1.5 text-sm font-medium bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg transition-colors">
                        ⚡ Generate Now
                    </button>
                </form>

                <form action="{{ route('admin.auto-publish.toggle-status', $autoPublish) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="text-sm font-medium bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white px-4 py-2 rounded-lg transition-colors">
                        @if ($autoPublish->status === 'active')
                            ⏸ Pause
                        @elseif($autoPublish->status === 'paused')
                            ▶ Resume
                        @else
                            🔄 Retry
                        @endif
                    </button>
                </form>

                <a href="{{ route('admin.auto-publish.edit', $autoPublish) }}"
                    class="text-sm font-medium bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white px-4 py-2 rounded-lg transition-colors">
                    ✏️ Edit
                </a>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            @php
                $stats = [
                    ['label' => 'Published', 'value' => $autoPublish->total_generated, 'icon' => '📄'],
                    ['label' => 'Days Remaining', 'value' => $autoPublish->remaining_days, 'icon' => '📅'],
                    ['label' => 'Posts / Day', 'value' => $autoPublish->blogs_per_day, 'icon' => '⚡'],
                    ['label' => 'Total Days', 'value' => $autoPublish->total_days, 'icon' => '📆'],
                ];
            @endphp
            @foreach ($stats as $stat)
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <div class="text-2xl mb-2">{{ $stat['icon'] }}</div>
                    <div class="text-2xl font-bold text-white">{{ $stat['value'] }}</div>
                    <div class="text-gray-500 text-xs mt-0.5">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>

        {{-- Progress --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 mb-8">
            <div class="flex justify-between text-sm text-gray-400 mb-3">
                <span class="font-medium text-gray-200">Overall Progress</span>
                <span>{{ $autoPublish->progress_percentage }}% — {{ $autoPublish->total_generated }} of approx.
                    {{ $autoPublish->total_days }} posts</span>
            </div>
            <div class="h-2.5 bg-gray-800 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-indigo-600 to-indigo-400 rounded-full transition-all duration-700"
                    style="width: {{ $autoPublish->progress_percentage }}%"></div>
            </div>
            @if ($autoPublish->last_run_at)
                <p class="text-gray-500 text-xs mt-2">Last run: {{ $autoPublish->last_run_at->diffForHumans() }}</p>
            @else
                <p class="text-gray-500 text-xs mt-2">Not run yet — will trigger on
                    {{ $autoPublish->start_date->format('d M Y') }} at 08:00.</p>
            @endif
        </div>

        {{-- Context Box --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 mb-8">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Content Context</h3>
            <p class="text-gray-300 text-sm leading-relaxed">{{ $autoPublish->context }}</p>
            @if ($autoPublish->category || $autoPublish->tags || $autoPublish->author)
                <div class="flex flex-wrap gap-4 mt-4 pt-4 border-t border-gray-800 text-sm">
                    @if ($autoPublish->category)
                        <span class="text-gray-500">Category: <span
                                class="text-gray-300">{{ $autoPublish->category }}</span></span>
                    @endif
                    @if ($autoPublish->author)
                        <span class="text-gray-500">Author: <span
                                class="text-gray-300">{{ $autoPublish->author }}{{ $autoPublish->author_role ? ', ' . $autoPublish->author_role : '' }}</span></span>
                    @endif
                    @if ($autoPublish->tags)
                        <span class="text-gray-500">Tags:
                            <span class="text-gray-300">{{ implode(', ', $autoPublish->tags) }}</span>
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Generated Blogs Table --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                <h2 class="font-semibold text-white">Generated Blogs</h2>
                <span class="text-gray-500 text-sm">{{ $blogs->total() }} total</span>
            </div>

            @if ($blogs->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="text-4xl mb-3">✍️</div>
                    <p class="text-gray-400 text-sm">No blogs generated yet.</p>
                    <p class="text-gray-600 text-xs mt-1">Use "Generate Now" to create the first post.</p>
                </div>
            @else
                <div class="divide-y divide-gray-800">
                    @foreach ($blogs as $blog)
                        <div class="flex items-start gap-4 p-5 hover:bg-gray-800/40 transition-colors group">
                            {{-- Thumbnail --}}
                            @if ($blog->thumbnail)
                                <img src="{{ $blog->thumbnail }}" alt=""
                                    class="w-16 h-12 rounded-lg object-cover bg-gray-800 shrink-0">
                            @else
                                <div
                                    class="w-16 h-12 rounded-lg bg-gray-800 shrink-0 flex items-center justify-center text-xl">
                                    📄</div>
                            @endif

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p
                                            class="text-white text-sm font-medium leading-snug line-clamp-1 group-hover:text-indigo-400 transition-colors">
                                            {{ $blog->title }}
                                        </p>
                                        <p class="text-gray-500 text-xs mt-0.5 line-clamp-1">
                                            {{ $blog->excerpt }}
                                        </p>
                                    </div>
                                    <span
                                        class="shrink-0 inline-flex items-center px-2 py-0.5 text-xs rounded-full border
                                    {{ $blog->status === 'published'
                                        ? 'bg-emerald-900/40 text-emerald-400 border-emerald-800'
                                        : 'bg-gray-800 text-gray-400 border-gray-700' }}">
                                        {{ ucfirst($blog->status) }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-3 mt-2 flex-wrap">
                                    @if ($blog->category)
                                        <span
                                            class="text-xs text-indigo-400 bg-indigo-950/40 border border-indigo-800/50 px-2 py-0.5 rounded-md">
                                            {{ $blog->category }}
                                        </span>
                                    @endif
                                    @if ($blog->read_time)
                                        <span class="text-xs text-gray-500">{{ $blog->read_time }} min read</span>
                                    @endif
                                    @if ($blog->published_at)
                                        <span
                                            class="text-xs text-gray-500">{{ $blog->published_at->format('d M Y, H:i') }}</span>
                                    @endif
                                    @if ($blog->tags)
                                        @foreach (array_slice((array) $blog->tags, 0, 3) as $tag)
                                            <span
                                                class="text-xs text-gray-500 bg-gray-800 px-2 py-0.5 rounded">{{ $tag }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            {{-- Action --}}
                            <div
                                class="shrink-0 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('admin.blog.edit', $blog) }}"
                                    class="text-xs text-gray-400 hover:text-white bg-gray-800 hover:bg-gray-700 px-2.5 py-1.5 rounded-lg transition-colors">
                                    Edit
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if ($blogs->hasPages())
                    <div class="px-6 py-4 border-t border-gray-800">
                        {{ $blogs->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
@endsection
