{{-- ── Top announcement / trust bar ──
     Full-width continuous marquee travelling left to right. The message list is
     rendered four times so the loop is seamless: the track starts shifted left
     by two copies and slides back to its origin, at which point the copy now
     under the viewport is identical to the one that started there. Speed is
     deliberately slow so each message can be read as it passes. Root font-size
     is 62.5%, so 1rem = 10px. --}}
<style>
    :root {
        --announce-h: 52px;
        --announce-h-mobile: 46px;
        /* Time for the track to travel one full copy of the message list.
           Kept long on purpose — the audit asked for a slower marquee. */
        --announce-cycle: 44s;
        --announce-cycle-mobile: 32s;
    }

    .announce-bar {
        position: relative;
        width: 100%;
        height: var(--announce-h);
        display: flex;
        align-items: center;
        overflow: hidden;
        background: linear-gradient(90deg, #4b3b2e 0%, #6b5236 45%, #4b3b2e 100%);
        border-bottom: 1px solid rgba(231, 200, 64, 0.35);
        z-index: 2;
    }

    /* Fade the two edges so items enter and leave softly. */
    .announce-bar::before,
    .announce-bar::after {
        content: "";
        position: absolute;
        top: 0;
        width: 56px;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }

    .announce-bar::before {
        left: 0;
        background: linear-gradient(90deg, #4b3b2e 0%, rgba(75, 59, 46, 0) 100%);
    }

    .announce-bar::after {
        right: 0;
        background: linear-gradient(270deg, #4b3b2e 0%, rgba(75, 59, 46, 0) 100%);
    }

    .announce-track {
        display: flex;
        align-items: center;
        width: max-content;
        /* Holds four copies of the list, so -50% is exactly two copies:
           start shifted left, then travel right back to origin. */
        transform: translateX(-50%);
        animation: announce-marquee var(--announce-cycle) linear infinite;
        will-change: transform;
    }

    .announce-group {
        display: flex;
        align-items: center;
    }

    .announce-bar:hover .announce-track,
    .announce-bar:focus-within .announce-track {
        animation-play-state: paused;
    }

    .announce-item {
        display: inline-flex;
        align-items: center;
        gap: 11px;
        font-family: inherit;
        font-size: 1.55rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
        color: #fdf6e3;
        line-height: 1.2;
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

    .announce-sep {
        display: inline-flex;
        align-items: center;
        padding: 0 30px;
        color: rgba(231, 200, 64, 0.6);
        font-size: 1.2rem;
        line-height: 1;
    }

    /* Left to right: the track slides from one copy back to its origin. */
    @keyframes announce-marquee {
        from {
            transform: translateX(-50%);
        }

        to {
            transform: translateX(0);
        }
    }

    @media (max-width: 767px) {
        .announce-bar {
            height: var(--announce-h-mobile);
        }

        .announce-bar::before,
        .announce-bar::after {
            width: 32px;
        }

        .announce-track {
            animation-duration: var(--announce-cycle-mobile);
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

        .announce-sep {
            padding: 0 20px;
        }
    }

    /* Accessibility: no motion — static, centred, single copy of the list. */
    @media (prefers-reduced-motion: reduce) {
        .announce-bar {
            height: auto;
            min-height: var(--announce-h);
            padding: 9px 16px;
            justify-content: center;
        }

        .announce-bar::before,
        .announce-bar::after {
            display: none;
        }

        .announce-track {
            width: 100%;
            transform: none;
            animation: none;
        }

        .announce-group {
            flex-wrap: wrap;
            justify-content: center;
        }

        .announce-group[aria-hidden="true"] {
            display: none;
        }

        .announce-item {
            padding: 3px 12px;
        }

        .announce-sep {
            padding: 0 4px;
        }
    }
</style>

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
@endphp

<div class="announce-bar" role="region" aria-label="Store highlights">
    <div class="announce-track">
        {{-- Four identical groups: the first half fills wide viewports, the
             second half is the seamless-loop duplicate. Only the very first
             group is exposed to assistive tech. --}}
        @for ($copy = 0; $copy < 4; $copy++)
            <div class="announce-group" @if ($copy > 0) aria-hidden="true" @endif>
                @foreach ($announcements as $announcement)
                    <span class="announce-item">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            {!! $announcement['icon'] !!}
                        </svg>
                        {{ $announcement['text'] }}
                    </span>
                    <span class="announce-sep" aria-hidden="true">&bull;</span>
                @endforeach
            </div>
        @endfor
    </div>
</div>
