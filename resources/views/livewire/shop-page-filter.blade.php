    <div>
        <style>
            /* Sidebar */
            .filter-sidebar {
                position: sticky;
                top: 80px;
                height: calc(100vh - 80px);
                background: #ffffff00;
            }

            .filter-scroll {
                height: 100%;
                overflow-y: auto;
                padding-right: 6px;
            }

            /* Card */
            .filter-card {
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                margin-bottom: 10px;
                overflow: hidden;
            }

            /* Card header */
            .filter-card-header {
                width: 100%;
                border: none;
                padding: 10px 12px;
                font-weight: 600;
                text-align: left;
                display: flex;
                justify-content: space-between;
                align-items: center;
                cursor: pointer;
                background: transparent;
            }

            /* Chevron */
            .chevron {
                width: 10px;
                height: 10px;
                border-right: 2px solid #ffffff;
                border-bottom: 2px solid #ffffff;
                transform: rotate(45deg);
                transition: transform .3s ease;
            }

            .filter-card.active .chevron {
                transform: rotate(-135deg);
            }

            /* Card body */
            .filter-card-body {
                max-height: 0;
                overflow: hidden;
                transition: max-height .35s ease;
                padding: 0 16px;
            }

            /* Sub group */
            .sub-group {
                margin-bottom: 10px;
            }

            .sub-title {
                font-size: 13px;
                font-weight: 600;
                margin: 8px 0;
            }

            /* Filter items */
            .filter-item {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 6px 0;
                cursor: pointer;
            }

            .filter-item input {
                width: 14px;
                height: 14px;
            }



            /* Standalone category inside parent */
            .standalone-group {
                /* margin-top: 12px;
                padding-top: 8px;*/
                border-top: 1px dashed #e5e7eb;
            }

            .standalone-title {
                font-size: 13px;
                font-weight: 700;
                letter-spacing: .03em;
                margin-bottom: 6px;
                color: #222;
            }

            .standalone-item {
                padding-left: 4px;
            }

            /* Selected badge on parent */
            .selected-badge {
                display: inline-block;
                margin-left: 6px;
                padding: 2px 6px;
                font-size: 11px;
                font-weight: 600;
                border-radius: 12px;
                background: #6b4f3b;
                color: #fff;
            }

            /* Optional: slight emphasis */
            .filter-card.has-selection .filter-card-header {
                background: #000000;
                color: #fff;
            }

            .filter-card.has-selection .filter-card-header .header-title {
                color: #fff;
            }

            .filter-card.has-selection .filter-card-header .chevron {
                border-color: #fff;
            }

            .filter-card.has-selection {
                border-color: #ffffff;
            }


            .filter-item.is-selected {
                background-color: #fcf0df;
                border-radius: 6px;
                font-weight: 600;
            }
        </style>

        <main class="main">
            <div class="page-header">
                <div class="container">
                    <div class="row" style="display: block;">
                        <div class="heading">
                            <h2 class="title  text-center">Shop</h2>
                            <span class="seprater-img"><img src="{{ asset('assets/img/seprater.png') }}"></span>
                        </div>
                    </div>
                </div><!-- End .container -->
            </div><!-- End .page-header -->
            <nav aria-label="breadcrumb" class="breadcrumb-nav">
                <div class="container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('index') }}">Home</a></li>
                        <li class="breadcrumb-item active"><a>Shop</a></li>
                    </ol>
                </div><!-- End .container -->
            </nav><!-- End .breadcrumb-nav -->

            <div class="page-content shop-page-f">
                <div class="container">
                    <div class="row shop-page-row">
                        {{-- @dd($selectedProductId) --}}
                        @if ($selectedProductId)
                            <div class="col-lg-9" id="product-content-area">
                                <livewire:product-details :product-id="$selectedProductId" :key="'product-' . $selectedProductId" />
                            </div>
                        @else
                            @if ($isProductListVisible)
                                <div class="col-lg-9" id="product-content-area">
                                    <div class="toolbox">
                                        <!--  <div class="toolbox-left">
                                    <div class="toolbox-info">
                                        Showing <span>{{ $products->count() }} of {{ $totalProductCount }}</span>
                                        Products
                                    </div>
                                </div> -->

                                        <div class="toolbox-right">
                                            <div class="toolbox-sort">
                                                <label for="sortby">Sort by:</label>
                                                <div class="select-custom">
                                                    <select wire:model="sort" wire:change="filter" id="sortby"
                                                        class="form-control">
                                                        <option value="name_asc">Name</option>
                                                        <option value="date">New</option>
                                                        <option value="price_low_high">Low to High</option>
                                                        <option value="price_high_low">High To Low</option>
                                                    </select>
                                                </div>
                                            </div><!-- End .toolbox-sort -->
                                        </div><!-- End .toolbox-right -->
                                    </div><!-- End .toolbox -->

                                    <div class="products mb-3">
                                        <div class="row">


                                            {{-- <x-product-list :products="$products" /> --}}

                                            <x-shop-page-product-list :products="$products" />


                                        </div>
                                    </div><!-- End .products -->

                                    @if ($isProductListVisible)
                                        <nav aria-label="Page navigation">
                                            @if (count($products) > 0)
                                                {{ $products->links() }}
                                            @endif
                                    @endif
                                    </nav>
                                </div><!-- End .col-lg-9 -->
                            @endif
                        @endif


                        <aside class="col-lg-3 order-lg-first">
                            <div class="sidebar sidebar-shop">
                                {{-- <div class="widget widget-clean">
                                    <label>Filter</label>
                                    <a class="sidebar-filter-clear" wire:click="clearFilter">Reset</a>
                                </div><!-- End .widget widget-clean --> --}}

                                <div class="widget widget-collapsible">
                                    <!-- <h3 class="widget-title">

                                        Category
                                    </h3> --><!-- End .widget-title -->

                                    <div class="collapse show" id="widget-1">
                                        <aside class="filter-sidebar">
                                            <div class="filter-scroll" id="categoryAccordion" wire:ignore>

                                                @foreach ($categories as $category)
                                                    @if ($category->sub_categories->count())
                                                        <div class="filter-card">

                                                            <button class="filter-card-header">
                                                                <span class="header-title">
                                                                    {{ strtoupper($category->title) }}
                                                                    <span class="selected-badge" hidden></span>
                                                                </span>
                                                                <span class="chevron"></span>
                                                            </button>
                                                            <div class="filter-card-body">

                                                                @foreach ($category->sub_categories as $sub)
                                                                    {{-- CASE 1: Sub-category has children (normal flow) --}}
                                                                    @if ($sub->sub_categories->count())
                                                                        <div class="sub-group">
                                                                            <div class="sub-title">
                                                                                {{ strtoupper($sub->title) }}</div>

                                                                            @foreach ($sub->sub_categories as $child)
                                                                                <label class="filter-item">
                                                                                    <input type="checkbox"
                                                                                        wire:model.live="selectedCategories"
                                                                                        value="{{ $child->cat_id }}">
                                                                                    <span
                                                                                        class="text">{{ $child->title }}</span>
                                                                                    <span
                                                                                        class="count">{{ $child->products->count() }}</span>
                                                                                </label>
                                                                            @endforeach
                                                                        </div>

                                                                        {{-- CASE 2: Sub-category has NO children but HAS products --}}
                                                                    @else
                                                                        <div class="standalone-group">
                                                                            <div class="standalone-title">
                                                                                {{ strtoupper($sub->title) }}
                                                                            </div>

                                                                            <label class="filter-item standalone-item">
                                                                                <input type="checkbox"
                                                                                    wire:model.live="selectedCategories"
                                                                                    value="{{ $sub->cat_id }}">
                                                                                <span
                                                                                    class="text">{{ $sub->title }}</span>
                                                                                <span
                                                                                    class="count">{{ $sub->products->count() }}</span>
                                                                            </label>
                                                                        </div>
                                                                    @endif
                                                                @endforeach

                                                            </div>

                                                        </div>
                                                    @else
                                                        <div class="filter-card"
                                                            data-category-id="{{ $category->cat_id }}">
                                                            {{-- @dd($category->cat_id, $selectedCategories) --}}

                                                            <button type="button" class="filter-card-header"
                                                                wire:click="toggleCategory({{ $category->cat_id }})">

                                                                {{-- hidden checkbox only for state reference (optional) --}}
                                                                <input type="checkbox" class="d-none"
                                                                    {{ in_array($category->cat_id, $selectedCategories) ? 'checked' : '' }}>

                                                                <span class="header-title">
                                                                    {{ strtoupper($category->title) }}
                                                                </span>
                                                                <span class="selected-badge" hidden></span>
                                                                <span class="chevron"></span>
                                                            </button>
                                                            <div class="filter-card-body">
                                                                <div class="standalone-group">
                                                                    @foreach ($category->products as $product)
                                                                        <label class="filter-item standalone-item"
                                                                            data-product-id="{{ $product->product_id }}">
                                                                            <input type="checkbox"
                                                                                wire:click="showProduct({{ $product->product_id }}, {{ $category->cat_id }})"
                                                                                value="{{ $product->product_id }}"
                                                                                {{ $selectedProductId == $product->product_id ? 'checked' : '' }}>
                                                                            {{-- ({{ $product->product_name }}) --}}
                                                                            <div class="product-label-filter">

                                                                                <img src="{{ asset('storage/' . $product->product_image) }}"
                                                                                    alt="">
                                                                                <span
                                                                                    class="text">{{ $product->product_name }}</span>
                                                                            </div>
                                                                            {{-- <span
                                                                                class="count">{{ $category->products->count() }}</span> --}}
                                                                            {{-- <span
                                                                                class="text">{{ $category->title }}</span>
                                                                            <span
                                                                                class="count">{{ $category->products->count() }}</span> --}}
                                                                        </label>
                                                                    @endforeach

                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach

                                            </div>
                                        </aside>


                                    </div><!-- End .collapse -->
                                </div><!-- End .widget -->
                                {{-- 
                                <div class="widget widget-collapsible">
                                    <h3 class="widget-title">
                                        <a data-toggle="collapse" href="#widget-5" role="button" aria-expanded="true"
                                            aria-controls="widget-5">
                                            Price
                                        </a>
                                    </h3><!-- End .widget-title -->

                                    <div class="collapse show" id="widget-5">
                                        <div class="widget-body">
                                            <div class="filter-price">
                                                <div class="filter-price-text">
                                                    Price Range:
                                                    <span id="filter-price-range"></span>
                                                </div><!-- End .filter-price-text -->

                                                <div id="price-slider"></div><!-- End #price-slider -->
                                            </div><!-- End .filter-price -->
                                        </div><!-- End .widget-body -->
                                    </div><!-- End .collapse -->
                                </div><!-- End .widget --> --}}
                            </div><!-- End .sidebar sidebar-shop -->
                        </aside><!-- End .col-lg-3 -->
                    </div><!-- End .row -->
                </div><!-- End .container -->
            </div><!-- End .page-content -->
        </main><!-- End .main -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const cards = document.querySelectorAll('.filter-card');

                function updateSelectionState(card) {
                    const checked = card.querySelectorAll('input[type="checkbox"]:checked');
                    const badge = card.querySelector('.selected-badge');
                    if (checked.length > 0) {
                        badge.textContent = checked.length;
                        badge.hidden = false;
                    } else {
                        badge.hidden = true;
                    }
                }

                function updateRowState(checkbox) {
                    const label = checkbox.closest('.filter-item');
                    if (!label) return;

                    if (checkbox.checked) {
                        label.classList.add('is-selected');
                    } else {
                        label.classList.remove('is-selected');
                    }
                }

                // Function to sync checkbox state with Livewire selectedProductId
                function syncProductCheckboxes(selectedProductId) {
                    document.querySelectorAll('.filter-item.standalone-item[data-product-id]').forEach(label => {
                        const productId = label.getAttribute('data-product-id');
                        const checkbox = label.querySelector('input[type="checkbox"]');
                        if (checkbox) {
                            const shouldBeChecked = productId == selectedProductId;
                            checkbox.checked = shouldBeChecked;
                            updateRowState(checkbox);

                            // Update the card badge
                            const card = checkbox.closest('.filter-card');
                            if (card) {
                                updateSelectionState(card);
                            }
                        }
                    });
                }

                cards.forEach(card => {
                    const header = card.querySelector('.filter-card-header');
                    const body = card.querySelector('.filter-card-body');

                    // Accordion behavior
                    header.addEventListener('click', () => {
                        cards.forEach(c => {
                            if (c !== card) {
                                c.classList.remove('active');
                                // c.classList.remove('has-selection');
                                c.querySelector('.filter-card-body').style.maxHeight = null;
                            }
                        });

                        card.classList.toggle('active');
                        body.style.maxHeight = card.classList.contains('active') ?
                            body.scrollHeight + 'px' :
                            null;
                    });

                    // Checkbox tracking for product checkboxes
                    card.querySelectorAll(
                        '.filter-item.standalone-item[data-product-id] input[type="checkbox"]').forEach(
                        cb => {
                            // On click - immediately update visual state
                            cb.addEventListener('click', (e) => {
                                const label = cb.closest('.filter-item');
                                const productId = label ? label.getAttribute('data-product-id') :
                                    null;

                                if (productId) {
                                    // Uncheck all other product checkboxes across all cards
                                    document.querySelectorAll(
                                        '.filter-item.standalone-item[data-product-id] input[type="checkbox"]'
                                    ).forEach(otherCb => {
                                        if (otherCb !== cb) {
                                            otherCb.checked = false;
                                            updateRowState(otherCb);
                                            const otherCard = otherCb.closest(
                                                '.filter-card');
                                            if (otherCard) {
                                                updateSelectionState(otherCard);
                                            }
                                        }
                                    });
                                }

                                updateRowState(cb);
                                updateSelectionState(card);
                            });

                            // Initial state
                            updateRowState(cb);
                        });

                    // Checkbox tracking for category checkboxes (non-product)
                    card.querySelectorAll('.filter-item:not([data-product-id]) input[type="checkbox"]').forEach(
                        cb => {
                            cb.addEventListener('change', () => {
                                updateRowState(cb);
                                updateSelectionState(card);
                            });

                            // Initial state
                            updateRowState(cb);
                        });

                    // Initial badge state
                    updateSelectionState(card);
                });

                // Re-initialize input spinners on new number inputs after Livewire updates
                function reinitInputSpinners() {
                    if ($.fn.inputSpinner) {
                        $(".product-details-quantity input[type='number']").each(function() {
                            if (!this['bootstrap-input-spinner']) {
                                $(this).inputSpinner({
                                    decrementButton: '<i class="icon-minus"></i>',
                                    incrementButton: '<i class="icon-plus"></i>',
                                    groupClass: 'input-spinner',
                                    buttonsClass: 'btn-spinner',
                                    buttonsWidth: '26px'
                                });
                            }
                        });
                    }
                }

                // Listen for Livewire updates to sync checkbox state
                if (typeof Livewire !== 'undefined') {
                    Livewire.hook('morph.updated', ({
                        el,
                        component
                    }) => {
                        // Re-sync after Livewire updates
                        const selectedProductId = component.snapshot.data.selectedProductId;
                        if (selectedProductId !== undefined) {
                            syncProductCheckboxes(selectedProductId);
                        }

                        // Re-initialize input spinners on newly rendered product list
                        setTimeout(() => reinitInputSpinners(), 50);
                    });

                    Livewire.hook('morph.added', ({
                        el
                    }) => {
                        // Re-initialize input spinners when new elements are added to DOM
                        setTimeout(() => reinitInputSpinners(), 50);
                    });

                    // Scroll to product content area after Livewire renders (mobile only)
                    function scrollToProductContent() {
                        if (window.innerWidth >= 992) return;

                        setTimeout(() => {
                            const target = document.getElementById('product-content-area');
                            if (!target) return;

                            const offset = 100; // 👈 adjust gap in px (navbar height, spacing, etc.)
                            const elementPosition = target.getBoundingClientRect().top;
                            const offsetPosition = elementPosition + window.pageYOffset - offset;

                            window.scrollTo({
                                top: offsetPosition,
                                behavior: 'smooth'
                            });
                        }, 150);
                    }

                    // Mark selected category card when header or product is clicked
                    Livewire.on('category-selected', (data) => {
                        const selectedId = String(data.categoryId);
                        document.querySelectorAll('.filter-card[data-category-id]').forEach(card => {
                            if (card.getAttribute('data-category-id') === selectedId) {
                                card.classList.add('has-selection');
                            } else {
                                card.classList.remove('has-selection');
                            }
                        });
                        // scrollToProductContent();
                    });

                    // Scroll when product detail is shown
                    Livewire.on('show-product-detail-slider', () => {
                        scrollToProductContent();
                    });
                }
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize main carousel

            });
        </script>
        <script>
            document.addEventListener('livewire:initialized', () => {

                let shouldScrollToProduct = false;

                // 1️⃣ Listen for your custom event
                Livewire.on('show-product-detail-slider', () => {
                    console.log('Event received: show-product-detail-slider');
                    shouldScrollToProduct = true;

                    // Initialize Owl AFTER Livewire updates DOM
                    setTimeout(initOwlCarousels, 100);
                });

                // 2️⃣ Run AFTER Livewire finishes DOM update
                Livewire.hook('message.processed', () => {

                    if (!shouldScrollToProduct) return;
                    if (window.innerWidth >= 992) return;

                    // Wait for Owl + browser paint
                    requestAnimationFrame(() => {
                        setTimeout(() => {
                            const target = document.getElementById('product-content-area');
                            if (!target) return;

                            const offset = 100;
                            const top =
                                target.getBoundingClientRect().top +
                                window.scrollY -
                                offset;

                            console.log('Scrolling to:', top);

                            window.scrollTo({
                                top,
                                behavior: 'smooth'
                            });

                            // 🔒 reset flag (VERY IMPORTANT)
                            shouldScrollToProduct = false;

                        }, 300); // allow Owl layout to settle
                    });
                });

                // 3️⃣ Owl carousel init function
                function initOwlCarousels() {
                    const $mainCarousel = $('.product-img-list');
                    const $thumbCarousel = $('.product-thumb-list');

                    if ($mainCarousel.hasClass('owl-loaded')) {
                        $mainCarousel.owlCarousel('destroy');
                    }

                    if ($thumbCarousel.hasClass('owl-loaded')) {
                        $thumbCarousel.owlCarousel('destroy');
                    }

                    // Main carousel
                    $mainCarousel.owlCarousel({
                        loop: true,
                        margin: 20,
                        nav: true,
                        items: 1,
                        dots: true,
                        autoplay: true,
                        autoplayTimeout: 3000,
                        autoplayHoverPause: true,
                        navText: [
                            '<i class="icon-angle-left"></i>',
                            '<i class="icon-angle-right"></i>'
                        ]
                    });

                    // Thumbnail carousel
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
                            1000: {
                                items: 4
                            }
                        }
                    });

                    // Activate first thumb
                    $('.product-thumb-list .item').first().addClass('active');

                    // Thumb click
                    $('.product-thumb-list .item').off('click').on('click', function() {
                        const index = $(this).data('index');

                        $('.product-thumb-list .item').removeClass('active');
                        $(this).addClass('active');

                        $mainCarousel.trigger('to.owl.carousel', [index, 300]);
                        $mainCarousel.trigger('stop.owl.autoplay');
                    });

                    // Sync thumbs
                    $mainCarousel.on('changed.owl.carousel', function(event) {
                        const index = event.item.index - event.relatedTarget._clones.length / 2;
                        const count = event.item.count;
                        const normalized = ((index % count) + count) % count;

                        $('.product-thumb-list .item').removeClass('active');
                        $('.product-thumb-list .item').eq(normalized).addClass('active');
                    });

                    console.log('Owl carousels initialized');
                }

            });
        </script>

    </div>
