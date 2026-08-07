{{-- ── Top announcement / trust bar (marquee) ── --}}
<style>
    :root {
        --announce-h: 38px;
        --announce-h-mobile: 34px;
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

    /* Soft fade on both edges so items enter/exit cleanly */
    .announce-bar::before,
    .announce-bar::after {
        content: '';
        position: absolute;
        top: 0;
        width: 48px;
        height: 100%;
        pointer-events: none;
        z-index: 2;
    }

    .announce-bar::before {
        left: 0;
        background: linear-gradient(90deg, #4b3b2e 0%, rgba(75, 59, 46, 0) 100%);
    }

    .announce-bar::after {
        right: 0;
        background: linear-gradient(270deg, #4b3b2e 0%, rgba(75, 59, 46, 0) 100%);
    }

    .announce-viewport {
        width: 100%;
        overflow: hidden;
    }

    .announce-track {
        display: flex;
        width: max-content;
        will-change: transform;
        animation: announce-scroll 28s linear infinite;
    }

    .announce-bar:hover .announce-track,
    .announce-bar:focus-within .announce-track {
        animation-play-state: paused;
    }

    .announce-group {
        display: flex;
        align-items: center;
        flex: 0 0 auto;
    }

    .announce-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0 28px;
        font-family: inherit;
        font-size: 1.25rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        white-space: nowrap;
        color: #fdf6e3;
        line-height: 1;
    }

    .announce-item svg {
        width: 16px;
        height: 16px;
        flex: 0 0 16px;
        stroke: #e7c840;
        stroke-width: 1.75;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .announce-sep {
        display: inline-flex;
        align-items: center;
        color: rgba(231, 200, 64, 0.6);
        font-size: 1.1rem;
        line-height: 1;
    }

    /* 4 identical groups in the track — shifting by exactly one group (-25%)
       loops seamlessly: text exits left and re-enters from the right. */
    @keyframes announce-scroll {
        from {
            transform: translate3d(0, 0, 0);
        }

        to {
            transform: translate3d(-25%, 0, 0);
        }
    }

    @media (max-width: 767px) {
        .announce-bar {
            height: var(--announce-h-mobile);
        }

        .announce-item {
            font-size: 1.1rem;
            padding: 0 18px;
            letter-spacing: 0.04em;
        }

        .announce-item svg {
            width: 14px;
            height: 14px;
            flex: 0 0 14px;
        }

        .announce-track {
            animation-duration: 20s;
        }

        .announce-bar::before,
        .announce-bar::after {
            width: 28px;
        }
    }

    /* Accessibility: no motion — static, centred, scrollable if needed */
    @media (prefers-reduced-motion: reduce) {
        .announce-track {
            animation: none;
            width: 100%;
            justify-content: center;
        }

        .announce-group[aria-hidden="true"] {
            display: none;
        }

        .announce-viewport {
            overflow-x: auto;
        }
    }
</style>

<div class="announce-bar" role="region" aria-label="Store highlights">
    <div class="announce-viewport">
        <div class="announce-track">
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
                        'icon' =>
                            '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 12h.01M18 12h.01"/>',
                    ],
                    [
                        'text' => 'Lab Tested Every Batch',
                        // Lucide: flask-conical
                        'icon' =>
                            '<path d="M10 2v7.31L4.7 18.6A2 2 0 0 0 6.43 21h11.14a2 2 0 0 0 1.73-2.4L14 9.31V2"/><path d="M8.5 2h7"/><path d="M7 15h10"/>',
                    ],
                ];
            @endphp

            {{-- Four identical groups = seamless -25% loop, wide enough to fill any viewport.
                 Only the first copy is exposed to screen readers. --}}
            @for ($copy = 0; $copy < 4; $copy++)
                <div class="announce-group" @if ($copy > 0) aria-hidden="true" @endif>
                    @foreach ($announcements as $i => $announcement)
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
</div>
