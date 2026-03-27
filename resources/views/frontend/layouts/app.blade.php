<!DOCTYPE html>
<html lang="UTF-8">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
    <title>Amma's Spices</title>


    <meta name="keywords"
        content="Indian spices online, homemade masala Delhi NCR India, authentic Indian spices, buy spices online Delhi NCR, pure spices India, traditional masalas, Amma style cooking, homemade taste spices, fresh ground spices, Delhi NCR spices.">
    <meta name="description"
        content="Discover Amma’s Spices for authentic Indian spices and homemade masalas. Fresh, pure and full of traditional flavor. Order premium spices online with delivery across India.">
    <meta property="og:title" content="">
    <meta property="og:description"
        content="Discover Amma’s Spices for authentic Indian spices and homemade masalas. Fresh, pure and full of traditional flavor. Order premium spices online with delivery across India.">
    <meta property="og:image" content="{{ asset('assets/images/homebg.jpg') }}">
    <meta property="og:url" content="ammasspices.com">
    <meta property="og:type" content="website">

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/icon/favicon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon/favicon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon/favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon/favicon.png') }}">
    <meta name="apple-mobile-web-app-title" content="">
    <meta name="application-name" content="">
    <meta name="msapplication-config" content="{{ asset('assets/images/icons/browserconfig.xml') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/vendor/line-awesome/line-awesome/line-awesome/css/line-awesome.min.css') }}">
    <!-- Plugins CSS File -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/owl-carousel/owl.carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/jquery.countdown.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/magnific-popup/magnific-popup.css') }}">
    <!-- Main CSS File -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/skins/skin-demo-28.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/demos/demo-28.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/demos/jgh-layout.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <style>
        /* :root {
            --gold: #e7c840;
            --gold-light: #f5e070;
            --gold-dim: rgba(231, 200, 64, 0.13);
            --bg: #0c0c0c;
            --bg2: #111111;
            --bg3: #181818;
            --card: #161616;
            --border: rgba(255, 255, 255, 0.07);
            --border2: rgba(255, 255, 255, 0.14);
            --text: #f0ece4;
            --text2: #a09888;
            --text3: #5e564c;
            --radius: 12px;
            --radius-lg: 18px;
        }


        

        html,
        body {
            overflow-x: hidden;
        }

        body {
            font: normal 300 1.4rem/1.86 "Inter", sans-serif;
            color: #ffffff;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: linear-gradient(rgb(0 0 0 / 22%), rgb(20 19 19 / 47%)),
                url({{ asset('assets/images/homebg.jpg') }}) no-repeat top center;
            background-size: cover;
            background-attachment: fixed;
        }

        @media (max-width: 768px) {
            body {
                background-image: linear-gradient(rgb(0 0 0 / 22%), rgb(20 19 19 / 47%)),
                    url({{ asset('assets/images/homebg-mobile.jpg') }});
                background-attachment: scroll !important;
               
            }
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

         body::before {
            content: "";
            position: fixed;
            inset: 0;
            background:
                linear-gradient(rgb(0 0 0 / 22%), rgb(20 19 19 / 47%)),
                url({{ asset('assets/images/homebg.jpg') }}) no-repeat top center;
            background-size: cover;
            background-attachment: fixed;
            z-index: -1;
        }  */
    </style>
</head>

<body>

    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>

    <!-- start header -->
    <div class="page-wrapper">
        @if (Route::currentRouteName() != 'customer.orders.order_success')
            {{-- <script src="{{ asset('assets/js/bootstrap-input-spinner.js') }}"></script> --}}
            @include('frontend.layouts.header')
        @endif

        @yield('content')

        @if (Route::currentRouteName() != 'customer.orders.order_success')
            @include('frontend.layouts.footer')
            {{-- <script src="{{ asset('assets/js/bootstrap-input-spinner.js') }}"></script> --}}
        @endif

    </div>


    <!-- Mobile Menu -->
    <div class="mobile-menu-overlay"></div><!-- End .mobil-menu-overlay -->

    <div class="mobile-menu-container">
        <div class="mobile-menu-wrapper">
            <span class="mobile-menu-close"><i class="icon-close"></i></span>

            <form action="#" method="get" class="mobile-search">
                <label for="mobile-search" class="sr-only">Search</label>
                <input type="search" class="form-control" name="mobile-search" id="mobile-search"
                    placeholder="Search in..." required>
                <button class="btn btn-primary" type="submit"><i class="icon-search"></i></button>
            </form>

            <nav class="mobile-nav">
                <ul class="mobile-menu">
                    <li class="megamenu-container active megamenu-list">
                        <a href="{{ route('index') }}" class=" active">Home</a>

                    </li>
                    <li class="megamenu-list">
                        <a href="{{ route('frontend.about-us') }}" class="">About</a>

                    </li>
                    <li class="">
                        <a href="{{ route('shop.page.index') }}">Shop</a>
                        <ul> @isset($mobileNavCategories)
                                @foreach ($mobileNavCategories as $category)
                                    <li>
                                        <a href="{{ route('getCatList', $category->slug) }}"
                                            class="{{ $category->sub_categories->count() ? 'sf-with-ul' : '' }}">{{ $category->title }}</a>
                                        @if ($category->sub_categories->count())
                                            <ul>
                                                @foreach ($category->sub_categories as $subCategory)
                                                    <li>
                                                        <a href="{{ route('getCatList', [$category->slug, $subCategory->slug]) }}"
                                                            class="{{ $subCategory->sub_categories->count() ? 'sf-with-ul' : '' }}">{{ $subCategory->title }}</a>
                                                        @if ($subCategory->sub_categories->count())
                                                            {{-- Child categories --}}
                                                            <ul>
                                                                @foreach ($subCategory->sub_categories as $childCategory)
                                                                    <li>
                                                                        <a
                                                                            href="{{ route('getCatList', [$category->slug, $subCategory->slug, $childCategory->slug]) }}">{{ $childCategory->title }}</a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @elseif ($subCategory->products->count())
                                                            {{-- Products of subcategory --}}
                                                            <ul>
                                                                @foreach ($subCategory->products as $product)
                                                                    <li>
                                                                        <a href="{{ route('single.product.view', $product->slug) }}"
                                                                            class="pro-i-nm">
                                                                            <div class="product-list-mo">
                                                                                <img src="{{ asset('storage/' . $product->product_image) }}"
                                                                                    alt="{{ $product->product_name }}">
                                                                                <span
                                                                                    class="pro-name">{{ $product->product_name }}</span>
                                                                            </div>
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @elseif ($category->products->count())
                                            {{-- Products directly under main category --}}
                                            <ul>
                                                @foreach ($category->products as $product)
                                                    <li>
                                                        <a href="{{ route('single.product.view', $product->slug) }}"
                                                            class="pro-i-nm">
                                                            <div class="product-list-mo">
                                                                <img src="{{ asset('storage/' . $product->product_image) }}"
                                                                    alt="{{ $product->product_name }}">
                                                                <span class="pro-name">{{ $product->product_name }}</span>
                                                            </div>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                            @endisset
                        </ul>
                    </li>

                    <li class="megamenu-list">
                        <a href="{{ route('contact-us.index') }}" class="">Contact Us</a>

                    </li>
                    @php
                        $ua = strtolower(request()->header('User-Agent'));

                        $isMobile = preg_match('/iphone|android|ipad|mobile|blackberry|opera mini/', $ua);
                    @endphp
                    <li>

                        @if ($isMobile)
                            @livewire('search-product')
                        @endif
                    </li>


                </ul>
            </nav><!-- End .mobile-nav -->
        </div><!-- End .mobile-menu-wrapper -->
    </div><!-- End .mobile-menu-container -->




    <!-- start sticky elements -->


    <!-- javascript libraries -->

    @livewireScripts
    <script>
        // document.addEventListener('livewire:init', () => {
        //     // Handle various Livewire request states
        //     Livewire.hook('request', ({
        //         options,
        //         payload,
        //         respond,
        //         succeed,
        //         fail
        //     }) => {

        //         // Handle failures (like 419, 500, etc.)
        //         fail(({
        //             status,
        //             content,
        //             preventDefault
        //         }) => {
        //             console.log('Request failed with status:', status);

        //             switch (status) {
        //                 case 419:
        //                     preventDefault();
        //                     console.log('CSRF token expired, refreshing...');
        //                     window.location.reload();
        //                     break;
        //                 case 500:
        //                     preventDefault();
        //                     alert('Server error. Please try again.');
        //                     break;
        //                 case 403:
        //                     preventDefault();
        //                     alert('Access denied.');
        //                     break;
        //             }
        //         });

        //         // Handle network errors/rejections
        //         respond(({
        //             status,
        //             response
        //         }) => {
        //             if (status === 419) {
        //                 window.location.reload();
        //             }
        //         });
        //     });
        // });


        function addTowishListClass(element) {
            console.log($(element).html()); // Log the HTML content
            $(element).addClass('liked'); // Add a class
        }
        //  document.addEventListener('livewire:initialized', () => {

        document.addEventListener('DOMContentLoaded', () => {
            // $(document).on('click', '.add-to-cart', function() {
            //     const productId = $(this).data('id');
            //     const product_quantity = $(this).data('product_quantity');

            //     // This sends a global event to all mounted Livewire components
            //     window.dispatchEvent(
            //         new CustomEvent('addToCart', {
            //             detail: {
            //                 product_id: productId
            //             }
            //         })
            //     );

            $(document).on('click', '.add-to-cart', function() {
                const $container = $(this).closest('.d-flex');
                const productId = $container.data('product-id');
                const quantity = parseInt($container.find('.product_quantity').val()) || 1;

                // Validate
                if (quantity < 1 || quantity > 10) {
                    alert('Quantity must be between 1 and 10');
                    return;
                }

                console.log("Adding to cart:", {
                    product_id: productId,
                    quantity: quantity
                });
                // Pass as separate parameters (for Solution 2)
                Livewire.dispatch('addToCart', {
                    product_id: productId,
                    quantity: quantity
                });
            });
            /* From laravel product list component with updated code  */

            $(document).on('click', '.add-to-cart-component', function() {
                const $container = $(this).closest('.product-card__footer');
                const productId = $(this).data('product-id');
                const quantity = parseInt($container.find('.product_quantity').val()) || 1;

                // Validate
                if (quantity < 1 || quantity > 10) {
                    alert('Quantity must be between 1 and 10');
                    return;
                }

                console.log("Adding to cart:", {
                    product_id: productId,
                    quantity: quantity
                });
                // Pass as separate parameters (for Solution 2)
                Livewire.dispatch('addToCart', {
                    product_id: productId,
                    quantity: quantity
                });
            });

            // console.log('addToCart dispatched with ID:', productId);
            // });

            $(document).on('click', '.add-to-wishlist', function() {
                const productId = $(this).data('id');
                console.log("productId", productId);
                // This sends a global event to all mounted Livewire components
                window.dispatchEvent(
                    new CustomEvent('addToWishlist', {
                        detail: {
                            product_id: productId
                        }
                    })
                );


                $(this).removeClass('add-to-wishlist');
                3
                $(this).addClass('remove-from-wishlist');
                $(this).addClass('liked');

                console.log('addToWishlist dispatched with ID:', productId);
            });


            $(document).on('click', '.remove-from-wishlist', function() {
                const productId = $(this).data('id');

                // This sends a global event to all mounted Livewire components
                window.dispatchEvent(
                    new CustomEvent('removeFromWishlist', {
                        detail: {
                            product_id: productId
                        }
                    })
                );


                $(this).removeClass('liked');
                $(this).removeClass('remove-from-wishlist');
                $(this).addClass('add-to-wishlist');

                console.log('removeWishlist dispatched with ID:', productId);
            });

            window.addEventListener('save-to-localstorage', function(event) {
                const data = event.detail;
                localStorage.setItem("max_price", data[0]['max_price']);
                console.log('Saved to localStorage:', data, data[0]['max_price']);
            });


        });
    </script>
    <!-- Toastr JS -->




    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.hoverIntent.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.waypoints.min.js') }}"></script>
    <script src="{{ asset('assets/js/superfish.min.js') }}"></script>
    <script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script>
    @if (Route::currentRouteName() != 'getCartItems')
        <script src="{{ asset('assets/js/bootstrap-input-spinner.js') }}"></script>
    @endif

    <script src="{{ asset('assets/js/jquery.plugin.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.countdown.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.magnific-popup.min.js') }}"></script>

    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/demos/demo-2.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        //      toastr.options = {
        //     "closeButton": true,
        //     "progressBar": true,
        //     "timeOut": 0,
        //     "extendedTimeOut": 0
        // };
    </script>
    @if (session('success'))
        <script>
            toastr.success("{{ session('success') }}");
        </script>
    @endif

    @if (session('error'))
        <script>
            toastr.error("{{ session('error') }}");
        </script>
    @endif
    <script>
        window.csrfToken = "{{ csrf_token() }}";
        window.authUser = "{{ auth()->user() }}";
    </script>
    @stack('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('toast', (event) => {
                console.log("type", event); // Just for testing

                const {
                    type,
                    message
                } = event[0] ?? event;


                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',

                    timeOut: 800, // 👈 visible for 1.5 seconds
                    extendedTimeOut: 500, // 👈 after hover
                    showDuration: 200, // 👈 fade in speed
                    hideDuration: 200, // 👈 fade out speed
                };

                // toastr.options = {
                //     "closeButton": true,
                //     "progressBar": true,
                //     "timeOut": 0,
                //     "extendedTimeOut": 0
                // };


                toastr[type](message);
            });

            Livewire.on('init-product-carousel', () => {
                setTimeout(function() {
                    var $mainCarousel = $('.product-img-list');
                    var $thumbCarousel = $('.product-thumb-list');

                    // Destroy existing instances to avoid duplicates
                    if ($mainCarousel.hasClass('owl-loaded')) {
                        $mainCarousel.trigger('destroy.owl.carousel');
                        $mainCarousel.html($mainCarousel.find('.owl-stage-outer').html())
                            .removeClass('owl-loaded');
                    }
                    if ($thumbCarousel.hasClass('owl-loaded')) {
                        $thumbCarousel.trigger('destroy.owl.carousel');
                        $thumbCarousel.html($thumbCarousel.find('.owl-stage-outer').html())
                            .removeClass('owl-loaded');
                    }

                    // Initialize main carousel
                    var $main = $mainCarousel.owlCarousel({
                        loop: true,
                        margin: 20,
                        nav: true,
                        items: 1,
                        dots: true,
                        autoplay: true,
                        autoplayTimeout: 3000,
                        autoplayHoverPause: true,
                        navText: ['<i class="icon-angle-left"></i>',
                            '<i class="icon-angle-right"></i>'
                        ],
                        responsive: {
                            0: {
                                items: 1
                            },
                            600: {
                                items: 1
                            },
                            1000: {
                                items: 1
                            }
                        }
                    });

                    // Initialize thumbnail carousel
                    $thumbCarousel.owlCarousel({
                        loop: false,
                        margin: 10,
                        nav: false,
                        dots: false,
                        items: 4,
                        responsive: {
                            0: {
                                items: 3
                            },
                            600: {
                                items: 3
                            },
                            768: {
                                items: 4
                            },
                            1000: {
                                items: 4
                            }
                        }
                    });

                    // Set first thumbnail as active
                    $thumbCarousel.find('.item').first().addClass('active');

                    // Click on thumbnail to change main image
                    $thumbCarousel.find('.item').on('click', function() {
                        var index = $(this).data('index');
                        $thumbCarousel.find('.item').removeClass('active');
                        $(this).addClass('active');
                        $main.trigger('to.owl.carousel', [index, 300]);
                        $main.trigger('stop.owl.autoplay');
                        setTimeout(function() {
                            $main.trigger('play.owl.autoplay', [3000]);
                        }, 5000);
                    });

                    // Sync thumbnails when main carousel changes
                    $main.on('changed.owl.carousel', function(event) {
                        var currentIndex = event.item.index;
                        var itemCount = event.item.count;
                        var actualIndex = (currentIndex - event.relatedTarget._clones
                            .length / 2) % itemCount;
                        var normalizedIndex = actualIndex < 0 ? itemCount +
                            actualIndex :
                            actualIndex;
                        $thumbCarousel.find('.item').removeClass('active');
                        $thumbCarousel.find('.item').eq(normalizedIndex).addClass(
                            'active');
                    });
                }, 150);
            });
        });
    </script>


    {{-- Add to wishlist in realtime --}}
    <script>
        $(document).ready(function() {
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            $('.add-to-wishlist').on('click', function(e) {
                e.preventDefault();

                let $button = $(this);
                let $icon = $button.find('i');

                // Check if already in wishlist (has 'fas' class for filled heart)
                let isInWishlist = $icon.hasClass('fas');

                if (isInWishlist) {
                    // Remove from wishlist
                    // $(this).removeClass('liked');
                    $icon.removeClass('fas').addClass('far');
                    $button.attr('data-bs-original-title', 'Add to wishlist');

                    // Optional: Show feedback
                    // showTooltipFeedback($button, 'Removed from wishlist');
                } else {
                    // Add to wishlist
                    $icon.removeClass('far').addClass('fas');
                    $button.attr('data-bs-original-title', 'Added to wishlist');

                    // Optional: Show feedback
                    // showTooltipFeedback($button, 'Added to wishlist');
                }
            });

            // Helper function to show temporary feedback
            function showTooltipFeedback($element, message) {
                // Get or create tooltip instance
                let tooltip = bootstrap.Tooltip.getInstance($element[0]);

                if (tooltip) {
                    tooltip.dispose();
                }

                // Create new tooltip with updated message
                tooltip = new bootstrap.Tooltip($element[0], {
                    title: message,
                    trigger: 'manual'
                });

                tooltip.show();

                // Auto-hide after 1.5 seconds
                setTimeout(function() {
                    tooltip.hide();
                    setTimeout(function() {
                        tooltip.dispose();
                        // Restore original tooltip
                        new bootstrap.Tooltip($element[0]);
                    }, 150);
                }, 1500);
            }
        });
    </script>


    {{-- Shop page filer removed  --}}

    <script>
        $('.sidebar-filter-clear').on('click', function(e) {
            e.preventDefault();

            // Remove filter_selected class from all elements
            $('.filter_selected').removeClass('filter_selected');

            // Optional: Clear any checked checkboxes
            $('.sidebar-filter input[type="checkbox"]').prop('checked', false);

            // Optional: Clear any selected radio buttons
            $('.sidebar-filter input[type="radio"]').prop('checked', false);

            // Optional: Reset any range sliders or inputs
            $('.sidebar-filter input[type="range"]').val(0);
            $('.sidebar-filter input[type="text"]').val('');

            // Optional: Trigger a filter update or page reload
            // window.location.href = window.location.pathname; // Reload without query params
        });
    </script>
</body>

</html>
