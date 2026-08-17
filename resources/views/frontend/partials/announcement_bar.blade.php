{{-- ── Top announcement / trust bar ──
     One message at a time, centred, cross-fading on a fixed step. Replaces the
     old continuous marquee: a reader no longer has to track moving text. The
     keyframe percentages are derived from the message count below, so adding or
     removing a message keeps the timing even. Root font-size is 62.5%, so
     1rem = 10px. --}}

@php
    $announcements = [
        [
            'text' => 'Pan-India Delivery',
            // Lucide: truck
            'icon' =>
                '<path d="M14 18V6a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h2"/><path d="M14 9h4l3 3v5a1 1 0 0 1-1 1h-1"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/><path d="M9 18h6"/>',
        ],
        [
            'text' => 'Cash on Delivery',
            // Lucide: banknote
            'icon' => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 12h.01M18 12h.01"/>',
        ],
        [
            'text' => 'Lab Tested Every Batch',
            // Lucide: flask-conical
            'icon' =>
                '<path d="M10 2v7.31L4.7 18.6A2 2 0 0 0 6.43 21h11.14a2 2 0 0 0 1.73-2.4L14 9.31V2"/><path d="M8.5 2h7"/><path d="M7 15h10"/>',
        ],
    ];

    $announceCount = max(count($announcements), 1);
    // Share of the full cycle each message owns, and the slice of that share
    // spent fading in or out.
    $announceSlice = 100 / $announceCount;
    $announceFade = min(4, $announceSlice / 4);
@endphp

<style>
    :root {
        --announce-h: 52px;
        --announce-h-mobile: 46px;
        /* Seconds each message holds before the next one takes over. */
        --announce-step: 4s;
    }

    .announce-bar {
        position: relative;
        width: 100%;
        height: var(--announce-h);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        padding: 0 20px;
        background: linear-gradient(90deg, #4b3b2e 0%, #6b5236 45%, #4b3b2e 100%);
        border-bottom: 1px solid rgba(231, 200, 64, 0.35);
        z-index: 2;
    }

    .announce-rotator {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }

    .announce-item {
        /* Pinned to the whole rotator rather than sized to the text: an
           absolutely positioned flex child does not honour justify-content,
           so each message centres itself instead. */
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 11px;
        padding: 0 8px;
        font-family: inherit;
        font-size: 1.55rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
        color: #fdf6e3;
        line-height: 1.2;
        opacity: 0;
        animation: announce-rotate calc(var(--announce-step) * {{ $announceCount }}) linear infinite;
        animation-delay: calc(var(--announce-step) * var(--announce-index));
    }

    .announce-item svg {
        width: 19px;
        height: 19px;
        flex: 0 0 19px;
        stroke: #e7c840;
        stroke-width: 1.75;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .announce-bar:hover .announce-item,
    .announce-bar:focus-within .announce-item {
        animation-play-state: paused;
    }

    /* Each message rises in, holds for its slice, then lifts out. */
    @keyframes announce-rotate {
        0% {
            opacity: 0;
            transform: translateY(7px);
        }

        {{ round($announceFade, 2) }}% {
            opacity: 1;
            transform: translateY(0);
        }

        {{ round($announceSlice - $announceFade, 2) }}% {
            opacity: 1;
            transform: translateY(0);
        }

        {{ round($announceSlice, 2) }}% {
            opacity: 0;
            transform: translateY(-7px);
        }

        100% {
            opacity: 0;
            transform: translateY(-7px);
        }
    }

    @media (max-width: 767px) {
        .announce-bar {
            height: var(--announce-h-mobile);
            padding: 0 14px;
        }

        .announce-item {
            font-size: 1.4rem;
            letter-spacing: 0.05em;
            gap: 9px;
        }

        .announce-item svg {
            width: 17px;
            height: 17px;
            flex: 0 0 17px;
        }
    }

    /* Accessibility: no motion — every message shown at once, static. */
    @media (prefers-reduced-motion: reduce) {
        .announce-bar {
            height: auto;
            min-height: var(--announce-h);
            padding: 9px 16px;
        }

        .announce-rotator {
            flex-wrap: wrap;
            gap: 6px 22px;
            height: auto;
        }

        .announce-item {
            position: static;
            inset: auto;
            width: auto;
            opacity: 1;
            transform: none;
            animation: none;
        }
    }
</style>

<div class="announce-bar" role="region" aria-label="Store highlights">
    <div class="announce-rotator">
        @foreach ($announcements as $index => $announcement)
            <span class="announce-item" style="--announce-index: {{ $index }}">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    {!! $announcement['icon'] !!}
                </svg>
                {{ $announcement['text'] }}
            </span>
        @endforeach
    </div>
</div>
