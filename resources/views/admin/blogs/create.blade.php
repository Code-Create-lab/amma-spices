@extends('admin.layout.app')

@section('content')

{{-- Dropzone CSS --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css">

<style>
/* ═══════════════════════════════════════════════════════
   BLOG CREATE FORM — Amma's Spices Admin
   Dark: #0d0d0d bg | #161616 card | #e7c840 gold
═══════════════════════════════════════════════════════ */

.bcf-wrap {
    padding: 24px 0 60px;
}

/* ── Page header ── */
.bcf-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
}

.bcf-header__left h1 {
    font-size: 22px;
    font-weight: 700;
    color: #f0f0f0;
    margin: 0 0 4px;
    letter-spacing: -0.01em;
}

.bcf-header__left p {
    font-size: 13px;
    color: #666;
    margin: 0;
}

.bcf-header__back {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 12.5px;
    font-weight: 600;
    color: #888;
    text-decoration: none;
    border: 1px solid #2a2a2a;
    background: #111;
    padding: 8px 18px;
    border-radius: 8px;
    transition: border-color 0.2s, color 0.2s;
}

.bcf-header__back:hover {
    border-color: #e7c840;
    color: #e7c840;
}

/* ── Two-column layout ── */
.bcf-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 22px;
    align-items: start;
}

@media (max-width: 1024px) {
    .bcf-layout { grid-template-columns: 1fr; }
}

/* ── Section card ── */
.bcf-card {
    background: #161616;
    border: 1px solid #222;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 20px;
}

.bcf-card__head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 20px;
    border-bottom: 1px solid #1e1e1e;
    background: #111;
}

.bcf-card__head-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(231,200,64,0.12);
    border: 1px solid rgba(231,200,64,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #e7c840;
}

.bcf-card__head h3 {
    font-size: 13.5px;
    font-weight: 700;
    color: #e0e0e0;
    margin: 0;
    letter-spacing: 0.02em;
}

.bcf-card__body { padding: 20px; }

/* ── Form label ── */
.bcf-label {
    display: block;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #777;
    margin-bottom: 7px;
}

.bcf-label span {
    color: #e7c840;
    margin-left: 2px;
}

/* ── Text inputs / textarea / select ── */
.bcf-input,
.bcf-select,
.bcf-textarea {
    width: 100%;
    background: #0d0d0d !important;
    border: 1px solid #2a2a2a !important;
    border-radius: 8px !important;
    color: #e0e0e0 !important;
    font-size: 14px;
    padding: 11px 14px !important;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
    font-family: inherit;
    line-height: 1.5;
}

.bcf-input:focus,
.bcf-select:focus,
.bcf-textarea:focus {
    border-color: #e7c840 !important;
    box-shadow: 0 0 0 3px rgba(231,200,64,0.08) !important;
    background: #0d0d0d !important;
    color: #e0e0e0 !important;
}

.bcf-input::placeholder,
.bcf-textarea::placeholder { color: #444 !important; }

.bcf-select option { background: #1a1a1a; color: #e0e0e0; }

.bcf-textarea { resize: vertical; min-height: 100px; }

/* Slug field — special with prefix */
.bcf-slug-wrap {
    display: flex;
    align-items: center;
    background: #0d0d0d;
    border: 1px solid #2a2a2a;
    border-radius: 8px;
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.bcf-slug-wrap:focus-within {
    border-color: #e7c840;
    box-shadow: 0 0 0 3px rgba(231,200,64,0.08);
}

.bcf-slug-prefix {
    padding: 11px 12px;
    font-size: 12px;
    color: #555;
    background: #111;
    border-right: 1px solid #2a2a2a;
    white-space: nowrap;
    flex-shrink: 0;
}

.bcf-slug-wrap input {
    flex: 1;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    color: #e0e0e0 !important;
    font-size: 13.5px;
    padding: 11px 14px !important;
    outline: none;
}

/* Field group spacing */
.bcf-field { margin-bottom: 18px; }
.bcf-field:last-child { margin-bottom: 0; }

/* Two-col inside card */
.bcf-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

@media (max-width: 640px) {
    .bcf-row { grid-template-columns: 1fr; }
}

/* ── Character counter ── */
.bcf-counter {
    display: flex;
    justify-content: flex-end;
    font-size: 11px;
    color: #555;
    margin-top: 5px;
    font-family: monospace;
}

.bcf-counter.warn { color: #e7c840; }
.bcf-counter.over { color: #e74c3c; }

/* ── DROPZONE ── */
.bcf-dropzone {
    border: 2px dashed #2a2a2a !important;
    border-radius: 10px !important;
    background: #0d0d0d !important;
    min-height: 160px !important;
    display: flex !important;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: border-color 0.25s, background 0.25s;
    padding: 20px !important;
}

.bcf-dropzone:hover,
.bcf-dropzone.dz-drag-hover {
    border-color: #e7c840 !important;
    background: rgba(231,200,64,0.03) !important;
}

.bcf-dropzone .dz-message {
    margin: 0 !important;
    text-align: center;
}

.bcf-dz-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(231,200,64,0.1);
    border: 1px solid rgba(231,200,64,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    color: #e7c840;
}

.bcf-dz-text {
    font-size: 13px;
    color: #888;
    line-height: 1.55;
}

.bcf-dz-text strong { color: #e7c840; display: block; margin-bottom: 3px; font-size: 14px; }
.bcf-dz-text span { font-size: 11px; color: #555; }

/* Dropzone preview */
.bcf-dropzone .dz-preview .dz-image {
    border-radius: 8px !important;
}

.bcf-dropzone .dz-preview .dz-remove {
    color: #e7c840 !important;
    font-size: 11px !important;
    border: 1px solid rgba(231,200,64,0.3) !important;
    border-radius: 4px !important;
    padding: 2px 6px !important;
}

/* Existing thumbnail preview */
.bcf-thumb-preview {
    margin-bottom: 12px;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #2a2a2a;
    position: relative;
}

.bcf-thumb-preview img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    display: block;
    filter: brightness(0.85);
}

.bcf-thumb-preview__lbl {
    position: absolute;
    bottom: 8px; left: 8px;
    background: rgba(10,10,10,0.8);
    color: #e7c840;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: 4px;
}

/* ── Tags input ── */
.bcf-tags-help {
    font-size: 11px;
    color: #555;
    margin-top: 5px;
}

/* ── Status toggle ── */
.bcf-status-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.bcf-status-option { position: relative; }

.bcf-status-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0; height: 0;
}

.bcf-status-option label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px;
    border: 1.5px solid #2a2a2a;
    border-radius: 8px;
    background: #0d0d0d;
    font-size: 12.5px;
    font-weight: 700;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.bcf-status-option input:checked + label {
    border-color: #e7c840;
    color: #e7c840;
    background: rgba(231,200,64,0.07);
}

.bcf-status-option label:hover {
    border-color: #444;
    color: #aaa;
}

/* ── Read time ── */
.bcf-rt-wrap {
    display: flex;
    align-items: center;
    gap: 0;
    background: #0d0d0d;
    border: 1px solid #2a2a2a;
    border-radius: 8px;
    overflow: hidden;
    transition: border-color 0.2s;
}

.bcf-rt-wrap:focus-within { border-color: #e7c840; }

.bcf-rt-wrap input {
    flex: 1;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    color: #e0e0e0 !important;
    font-size: 14px;
    padding: 11px 14px !important;
    outline: none;
    text-align: center;
}

.bcf-rt-suffix {
    padding: 11px 14px;
    font-size: 12px;
    color: #555;
    background: #111;
    border-left: 1px solid #2a2a2a;
    white-space: nowrap;
}

/* ── Submit buttons ── */
.bcf-actions {
    display: flex;
    gap: 10px;
    flex-direction: column;
}

.bcf-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 20px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    cursor: pointer;
    border: none;
    transition: all 0.22s;
    width: 100%;
    text-decoration: none;
}

.bcf-btn-publish {
    background: #e7c840;
    color: #0d0d0d;
}

.bcf-btn-publish:hover {
    background: #f0d050;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(231,200,64,0.3);
    color: #0d0d0d;
}

.bcf-btn-draft {
    background: #111;
    color: #888;
    border: 1.5px solid #2a2a2a;
}

.bcf-btn-draft:hover {
    border-color: #555;
    color: #ccc;
}

.bcf-btn-cancel {
    background: transparent;
    color: #555;
    border: 1.5px solid #1e1e1e;
    font-size: 12px;
}

.bcf-btn-cancel:hover { color: #e74c3c; border-color: rgba(231,76,60,0.3); }

/* ── SEO preview ── */
.bcf-seo-preview {
    background: #0d0d0d;
    border: 1px solid #1e1e1e;
    border-radius: 8px;
    padding: 14px 16px;
    margin-top: 14px;
}

.bcf-seo-preview__url {
    font-size: 11.5px;
    color: #4a9; /* green like Google */
    margin-bottom: 3px;
    word-break: break-all;
}

.bcf-seo-preview__title {
    font-size: 14px;
    color: #8ab4f8; /* Google blue */
    font-weight: 600;
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.bcf-seo-preview__desc {
    font-size: 12px;
    color: #888;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.bcf-seo-lbl {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #444;
    margin-bottom: 8px;
}

/* ── Helper text ── */
.bcf-help {
    font-size: 11px;
    color: #555;
    margin-top: 5px;
    line-height: 1.5;
}

/* ── Divider ── */
.bcf-divider {
    border: none;
    border-top: 1px solid #1e1e1e;
    margin: 18px 0;
}

/* ── Alert ── */
.bcf-alert {
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.bcf-alert-success {
    background: rgba(52,168,83,0.1);
    border: 1px solid rgba(52,168,83,0.25);
    color: #4caf7d;
}

.bcf-alert-danger {
    background: rgba(231,76,60,0.08);
    border: 1px solid rgba(231,76,60,0.2);
    color: #e06c5a;
}
</style>

<div class="bcf-wrap">
    <div class="container-fluid">

        {{-- Alerts --}}
        @if (session()->has('success'))
            <div class="bcf-alert bcf-alert-success">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path d="M20 6L9 17l-5-5"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bcf-alert bcf-alert-danger">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Page header --}}
        <div class="bcf-header">
            <div class="bcf-header__left">
                <h1>Create New Blog Post</h1>
                <p>Fill in the details below to publish a new article.</p>
            </div>
            <a href="{{ route('admin.blog.index') }}" class="bcf-header__back">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back to Blogs
            </a>
        </div>

        <form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data" id="bcfForm">
            @csrf

            <div class="bcf-layout">

                {{-- ══ LEFT COLUMN — Main content ══ --}}
                <div class="bcf-main">

                    {{-- ── Card: Article Details ── --}}
                    <div class="bcf-card">
                        <div class="bcf-card__head">
                            <div class="bcf-card__head-icon">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </div>
                            <h3>Article Details</h3>
                        </div>
                        <div class="bcf-card__body">

                            {{-- Title --}}
                            <div class="bcf-field">
                                <label class="bcf-label" for="title">Title <span>*</span></label>
                                <input type="text"
                                       id="title"
                                       name="title"
                                       class="bcf-input"
                                       placeholder="e.g. How to Make the Perfect Sambar"
                                       value="{{ old('title') }}"
                                       maxlength="120"
                                       required>
                                <div class="bcf-counter" id="titleCounter">0 / 120</div>
                            </div>

                            {{-- Slug --}}
                            <div class="bcf-field">
                                <label class="bcf-label" for="slug">URL Slug <span>*</span></label>
                                <div class="bcf-slug-wrap">
                                    <span class="bcf-slug-prefix">/blog/</span>
                                    <input type="text"
                                           id="slug"
                                           name="slug"
                                           placeholder="auto-generated-from-title"
                                           value="{{ old('slug') }}">
                                </div>
                                <div class="bcf-help">Auto-generated from title. Edit if needed.</div>
                            </div>

                            {{-- Category + Author --}}
                            <div class="bcf-row">
                                <div class="bcf-field">
                                    <label class="bcf-label" for="category">Category</label>
                                    <select id="category" name="category" class="bcf-select">
                                        <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select category</option>
                                        <option value="Recipe"      {{ old('category') === 'Recipe'      ? 'selected' : '' }}>Recipe</option>
                                        <option value="Spice Guide" {{ old('category') === 'Spice Guide' ? 'selected' : '' }}>Spice Guide</option>
                                        <option value="Tips & Tricks" {{ old('category') === 'Tips & Tricks' ? 'selected' : '' }}>Tips &amp; Tricks</option>
                                        <option value="Health"      {{ old('category') === 'Health'      ? 'selected' : '' }}>Health</option>
                                        <option value="Culture"     {{ old('category') === 'Culture'     ? 'selected' : '' }}>Culture</option>
                                    </select>
                                </div>
                                <div class="bcf-field">
                                    <label class="bcf-label" for="author">Author</label>
                                    <input type="text"
                                           id="author"
                                           name="author"
                                           class="bcf-input"
                                           placeholder="Amma's Kitchen"
                                           value="{{ old('author', "Amma's Kitchen") }}">
                                </div>
                            </div>

                            {{-- Author role --}}
                            <div class="bcf-field">
                                <label class="bcf-label" for="author_role">Author Role / Subtitle</label>
                                <input type="text"
                                       id="author_role"
                                       name="author_role"
                                       class="bcf-input"
                                       placeholder="Traditional South Indian Recipes &amp; Spice Expert"
                                       value="{{ old('author_role', 'Traditional South Indian Recipes & Spice Expert') }}">
                            </div>

                        </div>
                    </div>

                    {{-- ── Card: Content ── --}}
                    <div class="bcf-card">
                        <div class="bcf-card__head">
                            <div class="bcf-card__head-icon">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            </div>
                            <h3>Content</h3>
                        </div>
                        <div class="bcf-card__body">

                            {{-- Excerpt --}}
                            <div class="bcf-field">
                                <label class="bcf-label" for="excerpt">Excerpt (Listing Card Summary)</label>
                                <textarea id="excerpt"
                                          name="excerpt"
                                          class="bcf-textarea"
                                          rows="3"
                                          maxlength="200"
                                          placeholder="Short summary shown on the blog listing card (max 200 chars). Leave blank to auto-generate from content.">{{ old('excerpt') }}</textarea>
                                <div class="bcf-counter" id="excerptCounter">0 / 200</div>
                            </div>

                            {{-- Main content --}}
                            <div class="bcf-field">
                                <label class="bcf-label" for="content">Article Body <span>*</span></label>
                                <textarea id="content"
                                          name="content"
                                          class="bcf-textarea"
                                          rows="16"
                                          placeholder="Write your full article here. You can use HTML tags for formatting."
                                          required>{{ old('content') }}</textarea>
                                <div class="bcf-help">You can use basic HTML: &lt;h2&gt;, &lt;h3&gt;, &lt;p&gt;, &lt;strong&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;</div>
                            </div>

                        </div>
                    </div>

                    {{-- ── Card: SEO ── --}}
                    <div class="bcf-card">
                        <div class="bcf-card__head">
                            <div class="bcf-card__head-icon">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                            </div>
                            <h3>SEO Settings</h3>
                        </div>
                        <div class="bcf-card__body">

                            <div class="bcf-field">
                                <label class="bcf-label" for="meta_title">Meta Title</label>
                                <input type="text"
                                       id="meta_title"
                                       name="meta_title"
                                       class="bcf-input"
                                       placeholder="Leave blank to use article title"
                                       maxlength="60"
                                       value="{{ old('meta_title') }}">
                                <div class="bcf-counter" id="metaTitleCounter">0 / 60</div>
                            </div>

                            <div class="bcf-field">
                                <label class="bcf-label" for="meta_description">Meta Description</label>
                                <textarea id="meta_description"
                                          name="meta_description"
                                          class="bcf-textarea"
                                          rows="3"
                                          maxlength="160"
                                          placeholder="Leave blank to use excerpt">{{ old('meta_description') }}</textarea>
                                <div class="bcf-counter" id="metaDescCounter">0 / 160</div>
                            </div>

                            {{-- Live SEO preview --}}
                            <div class="bcf-seo-lbl">Google Preview</div>
                            <div class="bcf-seo-preview">
                                <div class="bcf-seo-preview__url" id="seoUrl">{{ url('/blog/') }}/<span id="seoSlug">your-slug-here</span></div>
                                <div class="bcf-seo-preview__title" id="seoTitle">Your blog title will appear here</div>
                                <div class="bcf-seo-preview__desc" id="seoDesc">Your meta description will appear here — keep it under 160 characters for best results.</div>
                            </div>

                        </div>
                    </div>

                </div>{{-- /.bcf-main --}}

                {{-- ══ RIGHT COLUMN — Sidebar ══ --}}
                <div class="bcf-sidebar">

                    {{-- ── Card: Publish ── --}}
                    <div class="bcf-card">
                        <div class="bcf-card__head">
                            <div class="bcf-card__head-icon">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                            <h3>Publish</h3>
                        </div>
                        <div class="bcf-card__body">

                            {{-- Status radio --}}
                            <div class="bcf-field">
                                <label class="bcf-label">Status</label>
                                <div class="bcf-status-group">
                                    <div class="bcf-status-option">
                                        <input type="radio" id="statusDraft" name="status" value="draft"
                                               {{ old('status', 'draft') === 'draft' ? 'checked' : '' }}>
                                        <label for="statusDraft">
                                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/></svg>
                                            Draft
                                        </label>
                                    </div>
                                    <div class="bcf-status-option">
                                        <input type="radio" id="statusPublished" name="status" value="published"
                                               {{ old('status') === 'published' ? 'checked' : '' }}>
                                        <label for="statusPublished">
                                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                                            Published
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Published date --}}
                            <div class="bcf-field" id="publishedAtField">
                                <label class="bcf-label" for="published_at">Publish Date</label>
                                <input type="datetime-local"
                                       id="published_at"
                                       name="published_at"
                                       class="bcf-input"
                                       value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}">
                            </div>

                            {{-- Read time --}}
                            <div class="bcf-field">
                                <label class="bcf-label" for="read_time">Read Time</label>
                                <div class="bcf-rt-wrap">
                                    <input type="number"
                                           id="read_time"
                                           name="read_time"
                                           min="1" max="60"
                                           placeholder="Auto"
                                           value="{{ old('read_time') }}">
                                    <span class="bcf-rt-suffix">min read</span>
                                </div>
                                <div class="bcf-help">Leave blank to auto-calculate from word count.</div>
                            </div>

                            <hr class="bcf-divider">

                            {{-- Action buttons --}}
                            <div class="bcf-actions">
                                <button type="submit" name="action" value="publish" class="bcf-btn bcf-btn-publish"
                                        onclick="document.getElementById('statusPublished').checked=true">
                                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                                    Publish Now
                                </button>
                                <button type="submit" name="action" value="draft" class="bcf-btn bcf-btn-draft">
                                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                    Save Draft
                                </button>
                                <a href="{{ route('admin.blog.index') }}" class="bcf-btn bcf-btn-cancel">
                                    Cancel
                                </a>
                            </div>

                        </div>
                    </div>

                    {{-- ── Card: Thumbnail ── --}}
                    <div class="bcf-card">
                        <div class="bcf-card__head">
                            <div class="bcf-card__head-icon">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                            <h3>Thumbnail Image</h3>
                        </div>
                        <div class="bcf-card__body">

                            {{-- Dropzone --}}
                            <div class="bcf-dropzone dropzone" id="thumbnailDropzone">
                                <div class="dz-message">
                                    <div class="bcf-dz-icon">
                                        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                                            <polyline points="17 8 12 3 7 8"/>
                                            <line x1="12" y1="3" x2="12" y2="15"/>
                                        </svg>
                                    </div>
                                    <div class="bcf-dz-text">
                                        <strong>Drop image here or click to upload</strong>
                                        <span>JPG, PNG, WEBP — Max 2MB</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Hidden real file input (populated by Dropzone) --}}
                            <input type="file" name="thumbnail" id="thumbnailInput" style="display:none" accept="image/*">

                            <div class="bcf-help" style="margin-top:10px;">
                                Recommended size: 1200 × 630px. Used as the hero image and listing card thumbnail.
                            </div>

                        </div>
                    </div>

                    {{-- ── Card: Tags ── --}}
                    <div class="bcf-card">
                        <div class="bcf-card__head">
                            <div class="bcf-card__head-icon">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                            </div>
                            <h3>Tags</h3>
                        </div>
                        <div class="bcf-card__body">
                            <div class="bcf-field">
                                <label class="bcf-label" for="tags">Tags</label>
                                <input type="text"
                                       id="tags"
                                       name="tags"
                                       class="bcf-input"
                                       placeholder="Sambar, South Indian, Dal Recipes"
                                       value="{{ old('tags') ? (is_array(old('tags')) ? implode(', ', old('tags')) : old('tags')) : '' }}">
                                <div class="bcf-tags-help">Separate tags with commas. These appear as clickable pills on the blog detail page.</div>
                            </div>

                            {{-- Live tag preview --}}
                            <div id="tagPreview" style="display:flex; flex-wrap:wrap; gap:7px; margin-top:12px;"></div>

                        </div>
                    </div>

                </div>{{-- /.bcf-sidebar --}}

            </div>{{-- /.bcf-layout --}}
        </form>

    </div>
</div>

@endsection

@section('postload-section')

{{-- Dropzone JS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>

<script>
Dropzone.autoDiscover = false;

(function () {

    // ── DROPZONE SETUP ─────────────────────────────────
    var dz = new Dropzone('#thumbnailDropzone', {
        url: '/',                   // We intercept before actual upload
        autoProcessQueue: false,    // Don't upload independently — submit with form
        maxFiles: 1,
        maxFilesize: 2,             // MB
        acceptedFiles: 'image/jpeg,image/png,image/webp',
        addRemoveLinks: true,
        dictRemoveFile: '✕ Remove',
        dictDefaultMessage: '',     // We use our own HTML message

        init: function () {
            var self = this;

            // When a file is added, push it into the hidden real input
            self.on('addedfile', function (file) {
                // Only one file allowed — remove previous
                if (self.files.length > 1) self.removeFile(self.files[0]);

                // Transfer to the hidden <input type="file"> so Laravel can read it
                var dt = new DataTransfer();
                dt.items.add(file);
                document.getElementById('thumbnailInput').files = dt.files;
                document.getElementById('thumbnailInput').name = 'thumbnail';
            });

            self.on('removedfile', function () {
                document.getElementById('thumbnailInput').value = '';
            });

            self.on('error', function (file, msg) {
                alert('Image error: ' + msg);
                self.removeFile(file);
            });
        }
    });

    // ── SLUG AUTO-GENERATE ─────────────────────────────
    var titleEl = document.getElementById('title');
    var slugEl  = document.getElementById('slug');
    var slugManuallyEdited = false;

    slugEl.addEventListener('input', function () {
        slugManuallyEdited = true;
    });

    titleEl.addEventListener('input', function () {
        if (!slugManuallyEdited) {
            slugEl.value = this.value
                .toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_]+/g, '-')
                .replace(/--+/g, '-');
        }
        updateSeoPreview();
        countChars('title', 'titleCounter', 120);
    });

    // ── CHARACTER COUNTERS ─────────────────────────────
    function countChars(inputId, counterId, max) {
        var el    = document.getElementById(inputId);
        var cEl   = document.getElementById(counterId);
        if (!el || !cEl) return;
        var len   = el.value.length;
        cEl.textContent = len + ' / ' + max;
        cEl.className = 'bcf-counter' + (len > max * 0.9 && len <= max ? ' warn' : len > max ? ' over' : '');
    }

    function attachCounter(inputId, counterId, max) {
        var el = document.getElementById(inputId);
        if (!el) return;
        el.addEventListener('input', function () { countChars(inputId, counterId, max); });
    }

    attachCounter('excerpt',          'excerptCounter',    200);
    attachCounter('meta_title',       'metaTitleCounter',  60);
    attachCounter('meta_description', 'metaDescCounter',   160);

    // ── SEO LIVE PREVIEW ───────────────────────────────
    function updateSeoPreview() {
        var title   = document.getElementById('title').value          || 'Your blog title will appear here';
        var mtitle  = document.getElementById('meta_title').value     || title;
        var mdesc   = document.getElementById('meta_description').value || (document.getElementById('excerpt').value || 'Your meta description will appear here.');
        var slug    = document.getElementById('slug').value           || 'your-slug-here';

        document.getElementById('seoTitle').textContent = mtitle;
        document.getElementById('seoDesc').textContent  = mdesc;
        document.getElementById('seoSlug').textContent  = slug;
    }

    ['title', 'meta_title', 'meta_description', 'excerpt', 'slug'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', updateSeoPreview);
    });

    // ── TAGS LIVE PREVIEW ──────────────────────────────
    var tagsInput   = document.getElementById('tags');
    var tagPreview  = document.getElementById('tagPreview');

    function renderTags() {
        var raw  = tagsInput.value;
        var tags = raw.split(',').map(function (t) { return t.trim(); }).filter(Boolean);
        tagPreview.innerHTML = tags.map(function (t) {
            return '<span style="background:#141414;border:1px solid #2a2a2a;color:#888;'
                 + 'font-size:11.5px;padding:4px 12px;border-radius:20px;font-family:sans-serif;">'
                 + escHtml(t) + '</span>';
        }).join('');
    }

    tagsInput.addEventListener('input', renderTags);

    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── PUBLISH DATE VISIBILITY ────────────────────────
    var publishedAtField = document.getElementById('publishedAtField');

    function togglePublishedAt() {
        var isDraft = document.getElementById('statusDraft').checked;
        publishedAtField.style.opacity  = isDraft ? '0.4' : '1';
        publishedAtField.style.pointerEvents = isDraft ? 'none' : 'auto';
    }

    document.querySelectorAll('input[name="status"]').forEach(function (r) {
        r.addEventListener('change', togglePublishedAt);
    });

    togglePublishedAt(); // init

    // ── FORM SUBMIT: set status from button clicked ────
    document.querySelectorAll('[name="action"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (this.value === 'publish') {
                document.getElementById('statusPublished').checked = true;
            } else {
                document.getElementById('statusDraft').checked = true;
            }
        });
    });

})();
</script>

@endsection