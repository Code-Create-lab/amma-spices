@extends('admin.layout.app')

@section('title', 'AI Auto-Publisher')

@section('content')
<div class="min-h-screen bg-gray-950 text-gray-100 p-6 lg:p-10">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-dark tracking-tight">
                ⚡ AI Auto-Publisher
            </h1>
            <p class="text-gray-400 text-sm mt-1">
                Schedules AI-generated blog posts published automatically on a daily cadence.
            </p>
        </div>
        <a href="{{ route('admin.auto-publish.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors shadow-lg shadow-indigo-900/40">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            New Schedule
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-900/50 border border-emerald-700 text-emerald-300 text-sm px-4 py-3 rounded-lg mb-6">
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-900/50 border border-red-700 text-red-300 text-sm px-4 py-3 rounded-lg mb-6">
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Schedules Grid --}}
    @if($schedules->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 text-center border border-dashed border-gray-700 rounded-2xl">
            <div class="text-5xl mb-4">🤖</div>
            <h3 class="text-lg font-semibold text-gray-300 mb-1">No schedules yet</h3>
            <p class="text-gray-500 text-sm mb-6">Create your first AI publishing schedule to get started.</p>
            <a href="{{ route('admin.auto-publish.create') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Create Schedule
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($schedules as $schedule)
                @php
                    $statusColors = [
                        'active'    => 'bg-emerald-500/20 text-emerald-400 border-emerald-700',
                        'paused'    => 'bg-yellow-500/20 text-yellow-400 border-yellow-700',
                        'completed' => 'bg-blue-500/20 text-blue-400 border-blue-700',
                        'failed'    => 'bg-red-500/20 text-red-400 border-red-700',
                    ];
                    $dotColors = [
                        'active'    => 'bg-emerald-400 animate-pulse',
                        'paused'    => 'bg-yellow-400',
                        'completed' => 'bg-blue-400',
                        'failed'    => 'bg-red-400',
                    ];
                    $color = $statusColors[$schedule->status] ?? 'bg-gray-700 text-gray-400 border-gray-600';
                    $dot   = $dotColors[$schedule->status] ?? 'bg-gray-400';
                @endphp

                <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden hover:border-gray-700 transition-colors group">

                    {{-- Card Header --}}
                    <div class="p-5 border-b border-gray-800">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('admin.auto-publish.show', $schedule) }}"
                                   class="text-white font-semibold text-base leading-snug hover:text-indigo-400 transition-colors line-clamp-2">
                                    {{ $schedule->name }}
                                </a>
                                <p class="text-gray-500 text-xs mt-1">
                                    {{ $schedule->ai_provider }} · {{ $schedule->ai_model }}
                                </p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full border {{ $color }} shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>
                                {{ ucfirst($schedule->status) }}
                            </span>
                        </div>

                        <p class="text-gray-400 text-sm mt-3 line-clamp-2">
                            {{ $schedule->context }}
                        </p>
                    </div>

                    {{-- Stats --}}
                    <div class="px-5 py-4 grid grid-cols-3 divide-x divide-gray-800">
                        <div class="text-center pr-4">
                            <div class="text-lg font-bold text-white">{{ $schedule->total_generated }}</div>
                            <div class="text-gray-500 text-xs mt-0.5">Published</div>
                        </div>
                        <div class="text-center px-4">
                            <div class="text-lg font-bold text-white">{{ $schedule->blogs_per_day }}</div>
                            <div class="text-gray-500 text-xs mt-0.5">Per Day</div>
                        </div>
                        <div class="text-center pl-4">
                            <div class="text-lg font-bold text-white">{{ $schedule->blogs_count }}</div>
                            <div class="text-gray-500 text-xs mt-0.5">Total Blogs</div>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="px-5 pb-4">
                        <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                            <span>{{ $schedule->start_date->format('d M') }} → {{ $schedule->end_date->format('d M Y') }}</span>
                            <span>{{ $schedule->progress_percentage }}%</span>
                        </div>
                        <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full transition-all"
                                 style="width: {{ $schedule->progress_percentage }}%"></div>
                        </div>
                    </div>

                    @if($schedule->last_error)
                        <div class="mx-5 mb-4 px-3 py-2 bg-red-950/50 border border-red-800 rounded-lg">
                            <p class="text-red-400 text-xs line-clamp-2">⚠ {{ $schedule->last_error }}</p>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="px-5 pb-5 flex items-center gap-2 flex-wrap">
                        <a href="{{ route('admin.auto-publish.show', $schedule) }}"
                           class="flex-1 text-center text-sm text-gray-300 hover:text-white bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-lg transition-colors">
                            View Blogs
                        </a>

                        <form action="{{ route('admin.auto-publish.trigger-now', $schedule) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit"
                                    class="w-full text-sm text-indigo-400 hover:text-white bg-indigo-950/50 hover:bg-indigo-600 border border-indigo-800 px-3 py-2 rounded-lg transition-colors"
                                    onclick="return confirm('Generate one blog post right now?')">
                                ⚡ Generate Now
                            </button>
                        </form>

                        <form action="{{ route('admin.auto-publish.toggle-status', $schedule) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="text-sm text-gray-400 hover:text-white bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-lg transition-colors">
                                @if($schedule->status === 'active') ⏸ Pause
                                @elseif($schedule->status === 'paused') ▶ Resume
                                @else 🔄 Retry
                                @endif
                            </button>
                        </form>

                        <a href="{{ route('admin.auto-publish.edit', $schedule) }}"
                           class="text-sm text-gray-400 hover:text-white bg-gray-800 hover:bg-gray-700 p-2 rounded-lg transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>

                        <form action="{{ route('admin.auto-publish.destroy', $schedule) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="text-sm text-gray-500 hover:text-red-400 bg-gray-800 hover:bg-red-950/50 p-2 rounded-lg transition-colors"
                                    onclick="return confirm('Delete this schedule? Published blogs will remain.')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $schedules->links() }}
        </div>
    @endif

</div>
@endsection