@extends('admin.layout.app')

@section('title', 'Edit Schedule — ' . $schedule->name)

@section('content')
    <div class="min-h-screen bg-gray-950 text-gray-100 p-6 lg:p-10">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('admin.auto-publish.index') }}" class="hover:text-gray-300 transition-colors">AI
                Auto-Publisher</a>
            <span>/</span>
            <a href="{{ route('admin.auto-publish.show', $schedule) }}"
                class="hover:text-gray-300 transition-colors line-clamp-1 max-w-xs">{{ $schedule->name }}</a>
            <span>/</span>
            <span class="text-gray-300">Edit</span>
        </nav>

        <div class="max-w-3xl">

            <div class="mb-8">
                <h1 class="text-2xl font-bold text-dark tracking-tight">Edit Schedule</h1>
                <p class="text-gray-400 text-sm mt-1">Changes take effect on the next scheduled run.</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-900/40 border border-red-700 rounded-xl p-4 mb-6">
                    <p class="text-red-400 text-sm font-medium mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-red-300 text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.auto-publish.update', $schedule) }}" method="POST" class="space-y-8">
                @csrf @method('PUT')

                {{-- Identity --}}
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">
                    <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-widest">Schedule Identity</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Schedule Name <span
                                class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $schedule->name) }}"
                            class="w-full bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors @error('name') border-red-600 @enderror">
                        @error('name')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Content Context <span
                                class="text-red-400">*</span></label>
                        <textarea name="context" rows="5"
                            class="w-full bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors resize-none @error('context') border-red-600 @enderror">{{ old('context', $schedule->context) }}</textarea>
                        @error('context')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Category</label>
                            <input type="text" name="category" value="{{ old('category', $schedule->category) }}"
                                class="w-full bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Tags <span
                                    class="text-gray-500 font-normal">(comma-separated)</span></label>
                            <input type="text" name="tags"
                                value="{{ old('tags', is_array($schedule->tags) ? implode(', ', $schedule->tags) : $schedule->tags) }}"
                                class="w-full bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Author Name</label>
                            <input type="text" name="author" value="{{ old('author', $schedule->author) }}"
                                class="w-full bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Author Role</label>
                            <input type="text" name="author_role"
                                value="{{ old('author_role', $schedule->author_role) }}"
                                class="w-full bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                        </div>
                    </div>
                </div>

                {{-- AI Config --}}
                {{-- <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">
                    <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-widest">AI Configuration</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Provider <span
                                    class="text-red-400">*</span></label>
                            <select name="ai_provider" id="providerSelect"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 cursor-pointer"
                                onchange="syncModels(this.value)">
                                @foreach ($models as $providerKey => $providerModels)
                                    @php
                                        $labels = [
                                            'openrouter' => 'OpenRouter (Multi-model)',
                                            'claude' => 'Claude Direct (Anthropic)',
                                            'openai' => 'OpenAI (GPT Models)',
                                        ];
                                    @endphp
                                    <option value="{{ $providerKey }}"
                                        {{ old('ai_provider', $schedule->ai_provider) === $providerKey ? 'selected' : '' }}>
                                        {{ $labels[$providerKey] ?? ucfirst($providerKey) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Model <span
                                    class="text-red-400">*</span></label>
                            <select name="ai_model" id="modelSelect"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 cursor-pointer">
                            </select>
                        </div>
                    </div>
                </div> --}}

                {{-- Schedule --}}
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">
                    <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-widest">Publishing Window</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Start Date <span
                                    class="text-red-400">*</span></label>
                            <input type="date" name="start_date"
                                value="{{ old('start_date', $schedule->start_date->toDateString()) }}"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors @error('start_date') border-red-600 @enderror">
                            @error('start_date')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">End Date <span
                                    class="text-red-400">*</span></label>
                            <input type="date" name="end_date"
                                value="{{ old('end_date', $schedule->end_date->toDateString()) }}"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors @error('end_date') border-red-600 @enderror">
                            @error('end_date')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Posts Per Day <span
                                    class="text-red-400">*</span></label>
                            <select name="blogs_per_day"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 cursor-pointer">
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}"
                                        {{ old('blogs_per_day', $schedule->blogs_per_day) == $i ? 'selected' : '' }}>
                                        {{ $i }} post{{ $i > 1 ? 's' : '' }} / day
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Status Override --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Status</label>
                        <select name="status"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 cursor-pointer">
                            @foreach (['active', 'paused', 'completed', 'failed'] as $s)
                                <option value="{{ $s }}"
                                    {{ old('status', $schedule->status) === $s ? 'selected' : '' }}>
                                    {{ ucfirst($s) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                    <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-widest mb-4">Internal Notes</h2>
                    <textarea name="notes" rows="3"
                        class="w-full bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors resize-none">{{ old('notes', $schedule->notes) }}</textarea>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-xl transition-colors">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.auto-publish.show', $schedule) }}"
                        class="text-sm text-gray-400 hover:text-white transition-colors">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const allModels = @json($models);
            const currentModel = "{{ old('ai_model', $schedule->ai_model) }}";

            function syncModels(provider) {
                const select = document.getElementById('modelSelect');
                select.innerHTML = '';
                const group = allModels[provider] || {};
                Object.entries(group).forEach(([value, label]) => {
                    const opt = document.createElement('option');
                    opt.value = value;
                    opt.textContent = label;
                    if (value === currentModel) opt.selected = true;
                    select.appendChild(opt);
                });
            }

            syncModels(document.getElementById('providerSelect').value);
            document.getElementById('providerSelect').addEventListener('change', e => syncModels(e.target.value));
        </script>
    @endpush
@endsection
