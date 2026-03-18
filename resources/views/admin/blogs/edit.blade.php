@extends('admin.layout.app')

@section('content')
    {{-- Dropzone CSS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css">

    <div class="bcf-wrap">
        <div class="container-fluid">

            {{-- Alerts --}}
            @if (session()->has('success'))
                <div class="bcf-alert bcf-alert-success">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                        style="flex-shrink:0;margin-top:1px">
                        <path d="M20 6L9 17l-5-5" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="bcf-alert bcf-alert-danger">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2" style="flex-shrink:0;margin-top:1px">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v4M12 16h.01" />
                    </svg>
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Page header --}}
            <div class="bcf-header">
                <div class="bcf-header__left">
                    <h1>
                        Edit Blog Post
                        <span class="bcf-status-badge {{ $blog->status }}">
                            @if ($blog->status === 'published')
                                <svg width="9" height="9" viewBox="0 0 10 10" fill="currentColor">
                                    <circle cx="5" cy="5" r="5" />
                                </svg>
                            @else
                                <svg width="9" height="9" viewBox="0 0 10 10" fill="none" stroke="currentColor"
                                    stroke-width="1.5">
                                    <circle cx="5" cy="5" r="4" />
                                </svg>
                            @endif
                            {{ ucfirst($blog->status) }}
                        </span>
                    </h1>
                    <p>Last updated {{ $blog->updated_at->diffForHumans() }} &nbsp;·&nbsp; Created
                        {{ $blog->created_at->format('d M Y') }}</p>
                </div>
                <div class="bcf-header__actions">
                    @if ($blog->status === 'published')
                        <a href="{{ route('customer.blog.show', $blog->slug) }}" target="_blank"
                            class="bcf-header__preview">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" />
                                <polyline points="15 3 21 3 21 9" />
                                <line x1="10" y1="14" x2="21" y2="3" />
                            </svg>
                            View Live
                        </a>
                    @endif
                    <a href="{{ route('admin.blog.index') }}" class="bcf-header__back">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path d="M19 12H5M12 19l-7-7 7-7" />
                        </svg>
                        Back to Blogs
                    </a>
                </div>
            </div>

            {{-- Info strip --}}
            <div class="bcf-info-strip">
                <div class="bcf-info-strip__item">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71" />
                        <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71" />
                    </svg>
                    <span>Slug: <strong>/blog/{{ $blog->slug }}</strong></span>
                </div>
                @if ($blog->published_at)
                    <div class="bcf-info-strip__item">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" />
                            <path d="M16 2v4M8 2v4M3 10h18" />
                        </svg>
                        <span>Published: <strong>{{ $blog->published_at->format('d M Y, h:i A') }}</strong></span>
                    </div>
                @endif
                <div class="bcf-info-strip__item">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 6v6l4 2" />
                    </svg>
                    <span>Read time: <strong>{{ $blog->read_time }} min</strong></span>
                </div>
            </div>

            <form action="{{ route('admin.blog.update', $blog->id) }}" method="POST" enctype="multipart/form-data"
                id="bcfForm">
                @csrf
                @method('PUT')

                <div class="bcf-layout">

                    {{-- ══ LEFT — Main content ══ --}}
                    <div class="bcf-main">

                        {{-- Article Details --}}
                        <div class="bcf-card">
                            <div class="bcf-card__head">
                                <div class="bcf-card__head-icon">
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </div>
                                <h3>Article Details</h3>
                            </div>
                            <div class="bcf-card__body">

                                <div class="bcf-field">
                                    <label class="bcf-label" for="title">Title <span>*</span></label>
                                    <input type="text" id="title" name="title" class="bcf-input"
                                        placeholder="e.g. How to Make the Perfect Sambar"
                                        value="{{ old('title', $blog->title) }}" maxlength="120" required>
                                    <div class="bcf-counter" id="titleCounter">{{ strlen(old('title', $blog->title)) }} /
                                        120</div>
                                </div>

                                <div class="bcf-field">
                                    <label class="bcf-label" for="slug">URL Slug <span>*</span></label>
                                    <div class="bcf-slug-wrap">
                                        <span class="bcf-slug-prefix">/blog/</span>
                                        <input type="text" id="slug" name="slug" placeholder="your-blog-slug"
                                            value="{{ old('slug', $blog->slug) }}">
                                    </div>
                                    <div class="bcf-help">Changing the slug will break existing links to this post.</div>
                                </div>

                                <div class="bcf-row">
                                    <div class="bcf-field">
                                        <label class="bcf-label" for="category">Category</label>
                                        <select id="category" name="category" class="bcf-select">
                                            <option value="" disabled>Select category</option>
                                            @foreach (['Recipe', 'Spice Guide', 'Tips & Tricks', 'Health', 'Culture'] as $cat)
                                                <option value="{{ $cat }}"
                                                    {{ old('category', $blog->category) === $cat ? 'selected' : '' }}>
                                                    {{ $cat }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="bcf-field">
                                        <label class="bcf-label" for="author">Author</label>
                                        <input type="text" id="author" name="author" class="bcf-input"
                                            placeholder="Amma's Kitchen" value="{{ old('author', $blog->author) }}">
                                    </div>
                                </div>

                                <div class="bcf-field">
                                    <label class="bcf-label" for="author_role">Author Role / Subtitle</label>
                                    <input type="text" id="author_role" name="author_role" class="bcf-input"
                                        placeholder="Traditional South Indian Recipes &amp; Spice Expert"
                                        value="{{ old('author_role', $blog->author_role) }}">
                                </div>

                            </div>
                        </div>

                        {{-- Content --}}
                        {{-- ── Content Card with Live Preview ── --}}
                        <div class="bcf-card">
                            <div class="bcf-card__head">
                                <div class="bcf-card__head-icon">
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="16" y1="13" x2="8" y2="13" />
                                        <line x1="16" y1="17" x2="8" y2="17" />
                                    </svg>
                                </div>
                                <h3>Content</h3>
                            </div>
                            <div class="bcf-card__body">

                                {{-- Excerpt --}}
                                <div class="bcf-field">
                                    <label class="bcf-label" for="excerpt">Excerpt</label>
                                    <textarea id="excerpt" name="excerpt" class="bcf-textarea" rows="3" maxlength="200"
                                        placeholder="Short summary for listing card. Leave blank to auto-generate.">{{ old('excerpt', isset($blog) ? $blog->excerpt : '') }}</textarea>
                                    <div class="bcf-counter" id="excerptCounter">
                                        {{ strlen(old('excerpt', isset($blog) ? $blog->excerpt ?? '' : '')) }} / 200
                                    </div>
                                </div>

                                {{-- Article Body + Live Preview split --}}
                                <div class="bcf-field">
                                    <label class="bcf-label" for="content">
                                        Article Body <span>*</span>
                                    </label>

                                    <div class="bcf-editor-wrap">

                                        {{-- Left: Editor --}}
                                        <div class="bcf-editor-pane">
                                            <div class="bcf-editor-pane__bar">
                                                <span class="bcf-editor-pane__label">
                                                    <svg width="12" height="12" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <polyline points="16 18 22 12 16 6" />
                                                        <polyline points="8 6 2 12 8 18" />
                                                    </svg>
                                                    HTML Editor
                                                </span>
                                                {{-- Quick-insert toolbar --}}
                                                <div class="bcf-editor-toolbar">
                                                    <button type="button" class="bcf-tb-btn" data-wrap="<h2>|</h2>"
                                                        title="Heading 2">H2</button>
                                                    <button type="button" class="bcf-tb-btn" data-wrap="<h3>|</h3>"
                                                        title="Heading 3">H3</button>
                                                    <button type="button" class="bcf-tb-btn" data-wrap="<p>|</p>"
                                                        title="Paragraph">P</button>
                                                    <button type="button" class="bcf-tb-btn"
                                                        data-wrap="<strong>|</strong>" title="Bold"><b>B</b></button>
                                                    <button type="button" class="bcf-tb-btn"
                                                        data-wrap="<ul>\n  <li>|</li>\n</ul>" title="List">UL</button>
                                                    <button type="button" class="bcf-tb-btn"
                                                        data-wrap="<ol>\n  <li>|</li>\n</ol>" title="Numbered">OL</button>
                                                    <button type="button" class="bcf-tb-btn" data-insert-tip
                                                        title="Tip box">💡</button>
                                                    <button type="button" class="bcf-tb-btn" data-insert-quote
                                                        title="Quote">"</button>
                                                    <button type="button" class="bcf-tb-btn" data-insert-figure
                                                        title="Figure/Image">🖼</button>
                                                </div>
                                            </div>

                                            <textarea id="blog_content" name="blog_content"
                                                placeholder="Write your article in HTML here...&#10;&#10;Example:&#10;&lt;p&gt;Your opening paragraph...&lt;/p&gt;&#10;&lt;h2&gt;Section Heading&lt;/h2&gt;&#10;&lt;p&gt;More content here...&lt;/p&gt;"
                                                required spellcheck="false">{{ old('blog_content', isset($blog) ? $blog->content : '') }}</textarea>
                                        </div>

                                        {{-- Right: Live Preview --}}
                                        <div class="bcf-preview-pane">
                                            <div class="bcf-preview-pane__bar">
                                                <span class="bcf-preview-pane__label">
                                                    <span class="bcf-preview-live-dot"></span>
                                                    Live Preview
                                                </span>
                                                <span class="bcf-wc-badge" id="wordCount">0 words</span>
                                            </div>
                                            <div class="bcf-preview-pane__scroll">
                                                <div id="livePreview" class="bcf-live">
                                                    {{-- empty state shown by JS --}}
                                                </div>
                                            </div>
                                        </div>

                                    </div>{{-- /.bcf-editor-wrap --}}

                                </div>

                            </div>
                        </div>


                        {{-- SEO --}}
                        <div class="bcf-card">
                            <div class="bcf-card__head">
                                <div class="bcf-card__head-icon">
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8" />
                                        <path d="M21 21l-4.35-4.35" />
                                    </svg>
                                </div>
                                <h3>SEO Settings</h3>
                            </div>
                            <div class="bcf-card__body">

                                <div class="bcf-field">
                                    <label class="bcf-label" for="meta_title">Meta Title</label>
                                    <input type="text" id="meta_title" name="meta_title" class="bcf-input"
                                        placeholder="Leave blank to use article title" maxlength="60"
                                        value="{{ old('meta_title', $blog->meta_title) }}">
                                    <div class="bcf-counter" id="metaTitleCounter">
                                        {{ strlen(old('meta_title', $blog->meta_title ?? '')) }} / 60</div>
                                </div>

                                <div class="bcf-field">
                                    <label class="bcf-label" for="meta_description">Meta Description</label>
                                    <textarea id="meta_description" name="meta_description" class="bcf-textarea" rows="3" maxlength="160"
                                        placeholder="Leave blank to use excerpt">{{ old('meta_description', $blog->meta_description) }}</textarea>
                                    <div class="bcf-counter" id="metaDescCounter">
                                        {{ strlen(old('meta_description', $blog->meta_description ?? '')) }} / 160</div>
                                </div>

                                <div class="bcf-seo-lbl">Google Preview</div>
                                <div class="bcf-seo-preview">
                                    <div class="bcf-seo-preview__url">{{ url('/blog/') }}/<span
                                            id="seoSlug">{{ $blog->slug }}</span></div>
                                    <div class="bcf-seo-preview__title" id="seoTitle">
                                        {{ $blog->meta_title ?: $blog->title }}</div>
                                    <div class="bcf-seo-preview__desc" id="seoDesc">
                                        {{ $blog->meta_description ?: ($blog->excerpt ?: 'Your meta description will appear here.') }}
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>{{-- /.bcf-main --}}

                    {{-- ══ RIGHT — Sidebar ══ --}}
                    <div class="bcf-sidebar">

                        {{-- Publish --}}
                        <div class="bcf-card">
                            <div class="bcf-card__head">
                                <div class="bcf-card__head-icon">
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                        <polyline points="22 4 12 14.01 9 11.01" />
                                    </svg>
                                </div>
                                <h3>Publish</h3>
                            </div>
                            <div class="bcf-card__body">

                                <div class="bcf-field">
                                    <label class="bcf-label">Status</label>
                                    <div class="bcf-status-group">
                                        <div class="bcf-status-option">
                                            <input type="radio" id="statusDraft" name="status" value="draft"
                                                {{ old('status', $blog->status) === 'draft' ? 'checked' : '' }}>
                                            <label for="statusDraft">
                                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                                </svg>
                                                Draft
                                            </label>
                                        </div>
                                        <div class="bcf-status-option">
                                            <input type="radio" id="statusPublished" name="status" value="published"
                                                {{ old('status', $blog->status) === 'published' ? 'checked' : '' }}>
                                            <label for="statusPublished">
                                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="2">
                                                    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                                                    <polyline points="16 7 22 7 22 13" />
                                                </svg>
                                                Published
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="bcf-field" id="publishedAtField">
                                    <label class="bcf-label" for="published_at">Publish Date</label>
                                    <input type="datetime-local" id="published_at" name="published_at" class="bcf-input"
                                        value="{{ old('published_at', $blog->published_at ? $blog->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
                                </div>

                                <div class="bcf-field">
                                    <label class="bcf-label" for="read_time">Read Time</label>
                                    <div class="bcf-rt-wrap">
                                        <input type="number" id="read_time" name="read_time" min="1"
                                            max="60" placeholder="Auto"
                                            value="{{ old('read_time', $blog->getRawOriginal('read_time')) }}">
                                        <span class="bcf-rt-suffix">min read</span>
                                    </div>
                                    <div class="bcf-help">Leave blank to auto-calculate.</div>
                                </div>

                                <hr class="bcf-divider">

                                <div class="bcf-actions">
                                    <button type="submit" name="action" value="publish"
                                        class="bcf-btn bcf-btn-publish"
                                        onclick="document.getElementById('statusPublished').checked=true">
                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2.5">
                                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                                            <polyline points="16 7 22 7 22 13" />
                                        </svg>
                                        {{ $blog->status === 'published' ? 'Update Post' : 'Publish Now' }}
                                    </button>
                                    <button type="submit" name="action" value="draft" class="bcf-btn bcf-btn-draft">
                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14z" />
                                            <polyline points="17 21 17 13 7 13 7 21" />
                                            <polyline points="7 3 7 8 15 8" />
                                        </svg>
                                        Save as Draft
                                    </button>
                                    <a href="{{ route('admin.blog.index') }}" class="bcf-btn bcf-btn-cancel">Cancel</a>

                                    <hr class="bcf-divider">

                                    <a href="{{ route('admin.blog.destroy', $blog->id) }}"
                                        onclick="return confirm('Permanently delete this blog post? This cannot be undone.')"
                                        class="bcf-btn-delete">
                                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6l-1 14H6L5 6" />
                                            <path d="M10 11v6M14 11v6" />
                                            <path d="M9 6V4h6v2" />
                                        </svg>
                                        Delete Post
                                    </a>
                                </div>

                            </div>
                        </div>

                        {{-- Thumbnail --}}
                        <div class="bcf-card">
                            <div class="bcf-card__head">
                                <div class="bcf-card__head-icon">
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" />
                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                        <polyline points="21 15 16 10 5 21" />
                                    </svg>
                                </div>
                                <h3>Thumbnail Image</h3>
                            </div>
                            <div class="bcf-card__body">

                                {{-- Show current thumbnail if exists --}}
                                @if ($blog->thumbnail)
                                    <div class="bcf-existing-thumb" id="currentThumb">
                                        <img src="{{ asset('storage/' . $blog->thumbnail) }}" alt="Current thumbnail">
                                        <div class="bcf-existing-thumb__overlay">
                                            <span class="bcf-existing-thumb__lbl">Current Image</span>
                                            <span class="bcf-existing-thumb__replace">Drop new image to replace</span>
                                        </div>
                                    </div>
                                @endif

                                <div class="bcf-dropzone dropzone" id="thumbnailDropzone">
                                    <div class="dz-message">
                                        <div class="bcf-dz-icon">
                                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                                                <polyline points="17 8 12 3 7 8" />
                                                <line x1="12" y1="3" x2="12" y2="15" />
                                            </svg>
                                        </div>
                                        <div class="bcf-dz-text">
                                            <strong>{{ $blog->thumbnail ? 'Replace image' : 'Upload thumbnail' }}</strong>
                                            <span>JPG, PNG, WEBP — Max 2MB</span>
                                        </div>
                                    </div>
                                </div>

                                <input type="file" name="thumbnail" id="thumbnailInput" style="display:none"
                                    accept="image/*">

                                <div class="bcf-help" style="margin-top:10px;">
                                    Recommended: 1200 × 630px. Uploading a new image replaces the current one.
                                </div>

                            </div>
                        </div>

                        {{-- Tags --}}
                        <div class="bcf-card">
                            <div class="bcf-card__head">
                                <div class="bcf-card__head-icon">
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <path
                                            d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" />
                                        <line x1="7" y1="7" x2="7.01" y2="7" />
                                    </svg>
                                </div>
                                <h3>Tags</h3>
                            </div>
                            <div class="bcf-card__body">
                                <div class="bcf-field">
                                    <label class="bcf-label" for="tags">Tags</label>
                                    <input type="text" id="tags" name="tags" class="bcf-input"
                                        placeholder="Sambar, South Indian, Dal Recipes"
                                        value="{{ old('tags', $blog->tags ? implode(', ', $blog->tags) : '') }}">
                                    <div class="bcf-tags-help">Separate with commas. These appear as pills on the blog
                                        detail page.</div>
                                </div>
                                <div id="tagPreview" style="display:flex; flex-wrap:wrap; gap:7px; margin-top:12px;">
                                </div>
                            </div>
                        </div>

                    </div>{{-- /.bcf-sidebar --}}

                </div>{{-- /.bcf-layout --}}
            </form>

        </div>
    </div>
@endsection

@section('postload-section')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>

    <script>
        Dropzone.autoDiscover = false;

        (function() {

            // ── DROPZONE ───────────────────────────────────────
            var dz = new Dropzone('#thumbnailDropzone', {
                url: '/',
                autoProcessQueue: false,
                maxFiles: 1,
                maxFilesize: 2,
                acceptedFiles: 'image/jpeg,image/png,image/webp',
                addRemoveLinks: true,
                dictRemoveFile: '✕ Remove',
                dictDefaultMessage: '',
                init: function() {
                    var self = this;
                    self.on('addedfile', function(file) {
                        if (self.files.length > 1) self.removeFile(self.files[0]);
                        var dt = new DataTransfer();
                        dt.items.add(file);
                        document.getElementById('thumbnailInput').files = dt.files;
                        // Hide the existing thumb preview once new image dropped
                        var cur = document.getElementById('currentThumb');
                        if (cur) cur.style.opacity = '0.35';
                    });
                    self.on('removedfile', function() {
                        document.getElementById('thumbnailInput').value = '';
                        var cur = document.getElementById('currentThumb');
                        if (cur) cur.style.opacity = '1';
                    });
                    self.on('error', function(file, msg) {
                        alert('Image error: ' + msg);
                        self.removeFile(file);
                    });
                }
            });

            // ── SLUG (manual edit only on edit page) ──────────
            var slugEl = document.getElementById('slug');
            var titleEl = document.getElementById('title');

            // On edit page slug is already set — only auto-update if user clears it
            titleEl.addEventListener('input', function() {
                if (!slugEl.value) {
                    slugEl.value = this.value
                        .toLowerCase().trim()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_]+/g, '-')
                        .replace(/--+/g, '-');
                }
                updateSeoPreview();
                countChars('title', 'titleCounter', 120);
            });

            // ── CHARACTER COUNTERS ────────────────────────────
            function countChars(inputId, counterId, max) {
                var el = document.getElementById(inputId);
                var cEl = document.getElementById(counterId);
                if (!el || !cEl) return;
                var len = el.value.length;
                cEl.textContent = len + ' / ' + max;
                cEl.className = 'bcf-counter' + (len > max * 0.9 && len <= max ? ' warn' : len > max ? ' over' : '');
            }

            function attachCounter(id, cId, max) {
                var el = document.getElementById(id);
                if (el) el.addEventListener('input', function() {
                    countChars(id, cId, max);
                });
            }

            attachCounter('excerpt', 'excerptCounter', 200);
            attachCounter('meta_title', 'metaTitleCounter', 60);
            attachCounter('meta_description', 'metaDescCounter', 160);

            // Init counters on load with pre-filled values
            countChars('title', 'titleCounter', 120);
            countChars('excerpt', 'excerptCounter', 200);
            countChars('meta_title', 'metaTitleCounter', 60);
            countChars('meta_description', 'metaDescCounter', 160);

            // ── SEO LIVE PREVIEW ──────────────────────────────
            function updateSeoPreview() {
                var title = document.getElementById('title').value || 'Your blog title';
                var mtitle = document.getElementById('meta_title').value || title;
                var mdesc = document.getElementById('meta_description').value || (document.getElementById('excerpt')
                    .value || 'Your meta description.');
                var slug = document.getElementById('slug').value || 'your-slug';

                document.getElementById('seoTitle').textContent = mtitle;
                document.getElementById('seoDesc').textContent = mdesc;
                document.getElementById('seoSlug').textContent = slug;
            }

            ['title', 'meta_title', 'meta_description', 'excerpt', 'slug'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) el.addEventListener('input', updateSeoPreview);
            });

            // ── TAGS PREVIEW ──────────────────────────────────
            var tagsInput = document.getElementById('tags');
            var tagPreview = document.getElementById('tagPreview');

            function renderTags() {
                var tags = tagsInput.value.split(',').map(function(t) {
                    return t.trim();
                }).filter(Boolean);
                tagPreview.innerHTML = tags.map(function(t) {
                    return '<span style="background:#141414;border:1px solid #2a2a2a;color:#888;' +
                        'font-size:11.5px;padding:4px 12px;border-radius:20px;font-family:sans-serif;">' +
                        esc(t) + '</span>';
                }).join('');
            }

            tagsInput.addEventListener('input', renderTags);
            renderTags(); // init with existing tags

            function esc(s) {
                return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }

            // ── PUBLISH DATE TOGGLE ───────────────────────────
            var publishedAtField = document.getElementById('publishedAtField');

            function togglePubAt() {
                var isDraft = document.getElementById('statusDraft').checked;
                publishedAtField.style.opacity = isDraft ? '0.4' : '1';
                publishedAtField.style.pointerEvents = isDraft ? 'none' : 'auto';
            }
            document.querySelectorAll('input[name="status"]').forEach(function(r) {
                r.addEventListener('change', togglePubAt);
            });
            togglePubAt();

            // ── SUBMIT: set status from button ────────────────
            document.querySelectorAll('[name="action"]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (this.value === 'publish') {
                        document.getElementById('statusPublished').checked = true;
                    } else {
                        document.getElementById('statusDraft').checked = true;
                    }
                });
            });

        })();
    </script>

    
{{-- ── Script — add at bottom of @section('postload-section') ── --}}
<script>
(function () {
 
    var editor  = document.getElementById('blog_content');
    var preview = document.getElementById('livePreview');
    var wcBadge = document.getElementById('wordCount');
    if (!editor || !preview) return;
 
    // ── LIVE PREVIEW RENDERER ─────────────────────────
    var renderTimer;
 
    function render() {
        var raw = editor.value.trim();
 
        // Word count
        var words = raw.replace(/<[^>]+>/g, ' ')
                       .replace(/\s+/g, ' ').trim();
        var wc = words ? words.split(' ').filter(Boolean).length : 0;
        if (wcBadge) wcBadge.textContent = wc + (wc === 1 ? ' word' : ' words');
 
        if (!raw) {
            preview.innerHTML =
                '<div class="bcf-preview-empty">' +
                '<svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">' +
                '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>' +
                '<polyline points="14 2 14 8 20 8"/>' +
                '<line x1="16" y1="13" x2="8" y2="13"/>' +
                '<line x1="16" y1="17" x2="8" y2="17"/>' +
                '</svg>' +
                '<p>Start writing in the editor<br>and your preview will appear here.</p>' +
                '</div>';
            return;
        }
 
        // Sanitise: only allow safe HTML tags — strip scripts/iframes
        var clean = raw
            .replace(/<script[\s\S]*?<\/script>/gi, '')
            .replace(/<iframe[\s\S]*?<\/iframe>/gi, '')
            .replace(/on\w+="[^"]*"/gi, '')
            .replace(/on\w+='[^']*'/gi, '');
 
        preview.innerHTML = clean;
    }
 
    // Debounce — render 300ms after typing stops
    editor.addEventListener('input', function () {
        clearTimeout(renderTimer);
        renderTimer = setTimeout(render, 300);
    });
 
    // Initial render (edit page has existing content)
    render();
 
    // ── TOOLBAR QUICK-INSERT ──────────────────────────
    document.querySelectorAll('.bcf-tb-btn[data-wrap]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var wrap   = btn.getAttribute('data-wrap');
            var parts  = wrap.split('|');
            var before = parts[0];
            var after  = parts[1] || '';
            insertAt(editor, before, after);
        });
    });
 
    // Tip box snippet
    var tipSnippet =
        '<div class="bsp-tip">\n' +
        '  <div class="bsp-tip__icon">💡</div>\n' +
        '  <div class="bsp-tip__text">\n' +
        '    <strong>Tip Title</strong>\n' +
        '    Your tip text goes here.\n' +
        '  </div>\n' +
        '</div>';
 
    // Pull quote snippet
    var quoteSnippet =
        '<div class="bsp-quote">\n' +
        '  <p>Your quote text goes here.</p>\n' +
        '  <cite>— Source Name</cite>\n' +
        '</div>';
 
    // Figure/image snippet
    var figureSnippet =
        '<figure class="bsp-figure">\n' +
        '  <img src="IMAGE_URL_HERE" alt="Description">\n' +
        '  <figcaption>Your caption here</figcaption>\n' +
        '</figure>';
 
    // Safe btn helper — avoids null errors on pages missing these buttons
    function onBtn(selector, snippet) {
        var el = document.querySelector(selector);
        if (el) { el.addEventListener('click', function () { insertAt(editor, snippet, ''); }); }
    }
 
    onBtn('[data-insert-tip]',    tipSnippet);
    onBtn('[data-insert-quote]',  quoteSnippet);
    onBtn('[data-insert-figure]', figureSnippet);
 
    // Insert text at cursor in textarea
    function insertAt(el, before, after) {
        var start = el.selectionStart;
        var end   = el.selectionEnd;
        var sel   = el.value.substring(start, end);
        var text  = before + sel + after;
        el.value  = el.value.substring(0, start) + text + el.value.substring(end);
        // Place cursor inside inserted tag
        var pos   = start + before.length + sel.length;
        el.setSelectionRange(pos, pos);
        el.focus();
        // Trigger live preview update
        clearTimeout(renderTimer);
        renderTimer = setTimeout(render, 100);
    }
 
    // ── TAB KEY → 2 spaces in textarea ───────────────
    editor.addEventListener('keydown', function (e) {
        if (e.key === 'Tab') {
            e.preventDefault();
            insertAt(editor, '  ', '');
        }
    });
 
})();
</script>
@endsection
