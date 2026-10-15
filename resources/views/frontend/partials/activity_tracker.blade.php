{{-- Activity tracker: time-on-page heartbeats + interaction events.
     The page-view uuid is set by TrackVisitorActivity middleware; absent on
     untracked pages (bots, disabled tracking), in which case this is a no-op. --}}
<script>
    (function () {
        var pv = @json(request()->attributes->get('activity_pv_uuid', ''));
        if (!pv) return;

        var beaconUrl = "{{ route('track.beacon') }}";
        var eventUrl = "{{ route('track.event') }}";
        var activeSeconds = 0;
        var lastSent = -1;
        var maxScroll = 0;
        var finalSent = false;

        setInterval(function () {
            if (document.visibilityState === 'visible') activeSeconds++;
        }, 1000);

        function currentScrollDepth() {
            var doc = document.documentElement;
            var total = doc.scrollHeight - window.innerHeight;
            if (total <= 0) return 100;
            var pct = Math.round(((window.pageYOffset || doc.scrollTop || 0) / total) * 100);
            return Math.min(Math.max(pct, 0), 100);
        }

        window.addEventListener('scroll', function () {
            var d = currentScrollDepth();
            if (d > maxScroll) maxScroll = d;
        }, { passive: true });

        function send(isFinal) {
            if (finalSent) return;
            if (!isFinal && activeSeconds === lastSent) return;
            lastSent = activeSeconds;
            var body = JSON.stringify({ pv: pv, s: activeSeconds, d: maxScroll });
            if (isFinal && navigator.sendBeacon) {
                navigator.sendBeacon(beaconUrl, new Blob([body], { type: 'text/plain' }));
                finalSent = true;
            } else {
                try {
                    fetch(beaconUrl, {
                        method: 'POST',
                        keepalive: true,
                        headers: { 'Content-Type': 'application/json' },
                        body: body
                    }).catch(function () {});
                } catch (e) {}
            }
        }

        setInterval(function () { send(false); }, 15000);
        window.addEventListener('pagehide', function () { send(true); });
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') send(false);
        });

        window.trackActivityEvent = function (type, productId, meta) {
            try {
                fetch(eventUrl, {
                    method: 'POST',
                    keepalive: true,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        pv: pv,
                        type: type,
                        product_id: productId || null,
                        meta: meta || {}
                    })
                }).catch(function () {});
            } catch (e) {}
        };

        // Wishlist is tracked client-side (mirrors the layout's delegated
        // handlers). Add-to-cart is tracked SERVER-side in NavCart /
        // ShopPageFilter / CartController via UserActivity::recordServerEvent,
        // because not every cart button carries a common CSS class.
        if (window.jQuery) {
            $(document).on('click', '.add-to-wishlist', function () {
                window.trackActivityEvent('wishlist_add', $(this).data('id'));
            });
            $(document).on('click', '.remove-from-wishlist', function () {
                window.trackActivityEvent('wishlist_remove', $(this).data('id'));
            });
        }
    })();
</script>
