@extends('admin.layout.app')

@section('title', 'New Auto-Publish Schedule')

@section('content')
    <div class="min-h-screen bg-gray-950 text-gray-100 p-6 lg:p-10">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('admin.auto-publish.index') }}" class="hover:text-gray-300 transition-colors">AI
                Auto-Publisher</a>
            <span>/</span>
            <span class="text-gray-300">New Schedule</span>
        </nav>

        <div class="max-w-3xl">

            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-white tracking-tight">Create Publishing Schedule</h1>
                <p class="text-gray-400 text-sm mt-1">
                    Define the topic context, date range, and AI model. Posts will be auto-published daily at 08:00.
                </p>
            </div>

            {{-- Validation Errors --}}
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

            <form action="{{ route('admin.auto-publish.store') }}" method="POST" class="space-y-8">
                @csrf

                {{-- Section 1: Basic Info --}}
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">
                    <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-widest">Schedule Identity</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">
                            Schedule Name <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            placeholder="e.g. Indian Stock Market — April 2026"
                            class="w-full bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors @error('name') border-red-600 @enderror">
                        @error('name')
                            <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1.5">
                            Content Context / Instructions <span class="text-red-400">*</span>
                        </label>
                        <textarea name="context" rows="5"
                            placeholder="Describe the blog topic in detail. Example: Write about Indian stock market trends, NSE/BSE analysis, SEBI regulations, and investment strategies for retail investors. Focus on beginner-friendly explanations with real market examples."
                            class="w-full bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors resize-none @error('context') border-red-600 @enderror">{{ old('context') }}</textarea>
                        <p class="text-gray-500 text-xs mt-1.5">
                            Be specific — better context = better blogs. Min 20, max 3000 characters.
                        </p>
                        @error('context')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Category</label>
                            <input type="text" name="category" value="{{ old('category') }}"
                                placeholder="e.g. Finance, Technology"
                                class="w-full bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Tags
                                <span class="text-gray-500 font-normal">(comma-separated)</span>
                            </label>
                            <input type="text" name="tags" value="{{ old('tags') }}"
                                placeholder="stocks, investing, NSE, trading"
                                class="w-full bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Author Name</label>
                            <input type="text" name="author" value="{{ old('author') }}" placeholder="e.g. Raj Mehta"
                                class="w-full bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Author Role</label>
                            <input type="text" name="author_role" value="{{ old('author_role') }}"
                                placeholder="e.g. Senior Market Analyst"
                                class="w-full bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors">
                        </div>
                    </div>
                </div>

                {{-- Section 2: AI Configuration --}}
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">
                    <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-widest">AI Configuration</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">
                                Provider <span class="text-red-400">*</span>
                            </label>
                            <select name="ai_provider" id="providerSelect"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors cursor-pointer"
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
                                        {{ old('ai_provider', 'openrouter') === $providerKey ? 'selected' : '' }}>
                                        {{ $labels[$providerKey] ?? ucfirst($providerKey) }}
                                    </option>
                                @endforeach
                            </select>

                            <p class="text-gray-500 text-xs mt-1.5">
                                OpenRouter gives access to multiple models via one key.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">
                                Model <span class="text-red-400">*</span>
                            </label>
                            <select name="ai_model" id="modelSelect"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors cursor-pointer">
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Section 3: Schedule Window --}}
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">
                    <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-widest">Publishing Window</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">
                                Start Date <span class="text-red-400">*</span>
                            </label>
                            <input type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}"
                                min="{{ now()->toDateString() }}"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors @error('start_date') border-red-600 @enderror">
                            @error('start_date')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">
                                End Date <span class="text-red-400">*</span>
                            </label>
                            <input type="date" name="end_date"
                                value="{{ old('end_date', now()->addDays(6)->toDateString()) }}"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors @error('end_date') border-red-600 @enderror">
                            @error('end_date')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">
                                Posts Per Day <span class="text-red-400">*</span>
                            </label>
                            <select name="blogs_per_day"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors cursor-pointer">
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}"
                                        {{ old('blogs_per_day', 1) == $i ? 'selected' : '' }}>
                                        {{ $i }} post{{ $i > 1 ? 's' : '' }} / day
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Quick Presets --}}
                    <div>
                        <p class="text-xs text-gray-500 mb-2">Quick date presets:</p>
                        <div class="flex flex-wrap gap-2">
                            @php
                                $presets = [
                                    '1 week' => ['days' => 6, 'label' => '1 Week'],
                                    '2 weeks' => ['days' => 13, 'label' => '2 Weeks'],
                                    '1 month' => ['days' => 29, 'label' => '1 Month'],
                                ];
                            @endphp
                            @foreach ($presets as $preset)
                                <button type="button" onclick="setDatePreset({{ $preset['days'] }})"
                                    class="text-xs text-indigo-400 hover:text-white bg-indigo-950/40 hover:bg-indigo-700 border border-indigo-800/60 px-3 py-1.5 rounded-lg transition-colors">
                                    {{ $preset['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Section 4: Notes --}}
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                    <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-widest mb-4">Internal Notes</h2>
                    <textarea name="notes" rows="3" placeholder="Optional — private notes for your reference. Not used by AI."
                        class="w-full bg-gray-800 border border-gray-700 text-white placeholder-gray-500 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 transition-colors resize-none">{{ old('notes') }}</textarea>
                </div>

                {{-- Submit --}}
                <div class="flex items-center gap-4">
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-xl transition-colors shadow-lg shadow-indigo-900/40">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Create Schedule
                    </button>
                    <a href="{{ route('admin.auto-publish.index') }}"
                        class="text-sm text-gray-400 hover:text-white transition-colors">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const allModels = @json($models);

            function syncModels(provider) {
                const select = document.getElementById('modelSelect');
                const oldVal = "{{ old('ai_model', 'anthropic/claude-3.5-sonnet') }}";
                select.innerHTML = '';
                const group = allModels[provider] || {};
                Object.entries(group).forEach(([value, label]) => {
                    const opt = document.createElement('option');
                    opt.value = value;
                    opt.textContent = label;
                    if (value === oldVal) opt.selected = true;
                    select.appendChild(opt);
                });
            }

            function setDatePreset(days) {
                const today = new Date();
                const end = new Date(today);
                end.setDate(today.getDate() + days);
                document.querySelector('[name="start_date"]').value = today.toISOString().split('T')[0];
                document.querySelector('[name="end_date"]').value = end.toISOString().split('T')[0];
            }

            // Init on load
            syncModels(document.getElementById('providerSelect').value);
            document.getElementById('providerSelect').addEventListener('change', e => syncModels(e.target.value));
        </script>
    @endpush
@endsection
