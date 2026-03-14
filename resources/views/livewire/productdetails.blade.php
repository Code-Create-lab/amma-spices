<div>

    <div class="page-contentin">
        <div class="containerd">
            <div class="product-details-top">
                <div class="row">
                    <div class="col-md-6">
                        <div class="product-gallery product-gallery-vertical">
                            <div class="row">
                                <div class="product-images-container">
                                    {{-- Main Image Carousel --}}
                                    <div class="owl-carousel owl-theme product-img-list" wire:ignore>
                                        @foreach ($response['images'] as $index => $media)
                                            @php
                                                $mediaSrc = asset('storage/' . $media['image']);
                                            @endphp
                                            <div class="item">
                                                <img src="{{ $mediaSrc }}" alt="Product Image {{ $index + 1 }}">
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Thumbnail Carousel --}}
                                    <div class="owl-carousel owl-theme product-thumb-list" wire:ignore>
                                        @foreach ($response['images'] as $index => $media)
                                            @php
                                                $mediaSrc = asset('storage/' . $media['image']);
                                            @endphp
                                            <div class="item" data-index="{{ $index }}">
                                                <img src="{{ $mediaSrc }}" alt="Thumbnail {{ $index + 1 }}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <style>
                                   
                                </style>
                            </div>
                        </div>
                    </div>
                    {{-- @dd($product) --}}
                    <div class="col-md-6">
                        <div class="product-details product-details-shop">
                            <h1 class="product-title">{{ $response['name'] }}</h1>

                            @php
                                $percentOff =
                                    $currentMrp > 0 && $currentPrice > 0
                                        ? (($currentMrp - $currentPrice) / $currentMrp) * 100
                                        : 0;
                            @endphp

                            <div class="ratings-container">
                                @if ($total_reviews > 0)
                                    @php
                                        $ratingPercent = ($avg_rating / 5) * 100;
                                    @endphp
                                    <div class="ratings">
                                        <div class="ratings-val" style="width: {{ $ratingPercent }}%;"></div>
                                    </div>
                                    <a class="ratings-text" id="review-link">
                                        ( {{ $total_reviews }} Reviews )
                                    </a>
                                @endif
                            </div>

                            <div class="product-price">
                                @if ($currentPrice != 0 && $currentMrp != $currentPrice)
                                    <span class="old-price 1"> ₹{{ number_format($currentMrp, 0) }}</span>
                                @endif

                                <span class="new-price">
                                    ₹{{ number_format($currentPrice == 0 ? $currentMrp : $currentPrice, 0, '.', ',') }}
                                </span>

                                @if ($currentPrice != 0 && $currentMrp != $currentPrice)
                                    <span class="discount">{{ (int) $percentOff }}%off</span>
                                @endif
                            </div>
                            <span class="gst-n-text">(GST Included)</span>

                            @if (!empty($response['variation_attributes']))
                                @foreach ($response['variation_attributes'] as $attributeName => $attributeValues)
                                    @php
                                        $sizeVariants = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'Size'];
                                        $isSize = in_array($attributeName, $sizeVariants);
                                        $displayName = $isSize ? 'Size' : 'Color';
                                        $attributeType = $isSize ? 'Size' : 'Color';
                                    @endphp

                                    <div
                                        class="d-flex align-items-center {{ $attributeType === 'Color' ? 'mb-20px color-options' : 'mb-35px size-options' }}">
                                        <label
                                            class="text-dark-gray alt-font me-15px fw-500">{{ $attributeType }}</label>

                                        @if ($attributeType === 'Color')
                                            <ul class="shop-color mb-0">
                                                @foreach ($attributeValues as $index => $attribute)
                                                    @php
                                                        $hasStock = false;
                                                        if (!empty($response['variations'])) {
                                                            foreach ($response['variations'] as $variation) {
                                                                if ($variation['stock'] > 0) {
                                                                    foreach ($variation['attributes'] as $attr) {
                                                                        if (
                                                                            $attr['name'] === 'Color' &&
                                                                            strtolower($attr['value']) ===
                                                                                strtolower($attribute['value'])
                                                                        ) {
                                                                            $hasStock = true;
                                                                            break 2;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        $stockClass = $hasStock ? '' : 'out-of-stock-variation';
                                                        $colorValue = strtolower(
                                                            str_replace(' ', '', $attribute['value']),
                                                        );
                                                        $uniqueId = 'color-' . ($index + 1);
                                                        $isSelected =
                                                            isset($selectedAttributes['Color']) &&
                                                            $selectedAttributes['Color']['value'] === $colorValue;
                                                    @endphp
                                                    <li>
                                                        <input class="d-none" type="radio" id="{{ $uniqueId }}"
                                                            name="color" {{ $isSelected ? 'checked' : '' }}>
                                                        <label for="{{ $uniqueId }}">
                                                            <span {{ !$hasStock ? 'title=Out-of-Stock' : '' }}
                                                                wire:click="selectAttribute('Color', '{{ $attribute['value'] }}', {{ $attribute['id'] }})"
                                                                class="attribute-btn {{ $isSelected ? 'selected attribute_seletion' : '' }} {{ $stockClass }}"
                                                                style="background-color: {{ $colorValue }}; cursor: pointer;">
                                                            </span>
                                                        </label>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <ul class="shop-size mb-0">
                                                @foreach ($attributeValues as $index => $attribute)
                                                    @php
                                                        $hasStock = false;
                                                        if (!empty($response['variations'])) {
                                                            foreach ($response['variations'] as $variation) {
                                                                if ($variation['stock'] > 0) {
                                                                    foreach ($variation['attributes'] as $attr) {
                                                                        if (
                                                                            $attr['name'] === 'Size' &&
                                                                            strtolower($attr['value']) ===
                                                                                strtolower($attribute['value'])
                                                                        ) {
                                                                            $hasStock = true;
                                                                            break 2;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        $stockClass = $hasStock ? '' : 'out-of-stock-variation';
                                                        $sizeValue = strtolower(
                                                            str_replace(' ', '', $attribute['value']),
                                                        );
                                                        $uniqueId = 'size-' . ($index + 1);
                                                        $isSelected =
                                                            isset($selectedAttributes['Size']) &&
                                                            $selectedAttributes['Size']['value'] === $sizeValue;
                                                    @endphp
                                                    <li>
                                                        <input class="d-none" type="radio" id="{{ $uniqueId }}"
                                                            name="size" {{ $isSelected ? 'checked' : '' }}>
                                                        <label for="{{ $uniqueId }}">
                                                            <span {{ !$hasStock ? 'title=Out-of-Stock' : '' }}
                                                                wire:click="selectAttribute('Size', '{{ $attribute['value'] }}', {{ $attribute['id'] }})"
                                                                class="attribute-btn {{ $isSelected ? 'selected attribute_seletion' : '' }} {{ $stockClass }}"
                                                                style="cursor: pointer;">
                                                                {{ strtoupper($attribute['value']) }}
                                                            </span>
                                                        </label>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                @endforeach
                            @endif

                            <div class="product-content">
                                {!! $response['description'] !!}
                            </div>

                            <div class="details-filter-row details-row-size">
                                <label for="qty">Qty:</label>
                                <div class="product-details-quantity product-shop-quantity" wire:ignore>
                                    {{-- <button type="button" wire:click="decrementQuantity" class="btn-minus">-</button> --}}
                                     @php
                                                    $cartQty = 1;

                                                    if (auth()->check()) {
                                                        // DB cart
                                                        $cartQty = $product->cart->quantity ?? 1;
                                                    } else {
                                                        // Session cart
                                                        $cartKey = $product->product_id . '-' . $product->variation->id;
                                                        $cartQty = session("cart.$cartKey.quantity", 1);
                                                    }
                                                @endphp
                                    <input type="number" value="{{ $cartQty }}" id="qty" class="form-control qty" wire:model="quantity"
                                        min="1" max="10" readonly>
                                    {{-- <button type="button" wire:click="incrementQuantity" class="btn-plus">+</button> --}}
                                </div>
                            </div>

                            <div class="product-details-action">
                                <a wire:click="addToCart" class="btn-product btn-cart add-to-cartJS"
                                    style="cursor: pointer;">
                                    <span>add to cart</span>
                                </a>

                                <div class="details-action-wrapper">
                                    <a wire:click="toggleWishlist" data-id="{{ $response['id'] }}"
                                        class="{{ $isInWishlist ? 'remove-from-wishlist liked' : 'add-to-wishlist' }} wishlist btn-product btn-wishlist"
                                        title="Wishlist" style="cursor: pointer;">
                                        <svg class="wish-icon" title="Like Safebox SVG File" width="21"
                                            height="21" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path
                                                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                                            </path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="product-details-tab" >
                <ul class="nav nav-pills justify-content-center" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="product-info-link" data-toggle="tab" href="#product-info-tab"
                            role="tab" aria-controls="product-info-tab" aria-selected="false">Description</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="product-review-link" data-toggle="tab" href="#product-review-tab"
                            role="tab" aria-controls="product-review-tab" aria-selected="false">
                            Reviews ({{ $total_reviews }})
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <img src="https://bodhi-bliss.yugasa.org/assets/img/bg-feture.png" class="bg-feture-sec">

                    <div class="tab-pane fade show active" id="product-info-tab" role="tabpanel"
                        aria-labelledby="product-info-link">
                        <div class="product-desc-content">
                            {!! $response['info'] !!}
                        </div>
                    </div>

                    <div class="tab-pane fade" id="product-review-tab" role="tabpanel"
                        aria-labelledby="product-review-link">
                        <div class="reviews">
                            <h3>Reviews ({{ count($top_reviews) }})</h3>
                            @foreach ($top_reviews as $review)
                                <div class="review">
                                    <div class="row no-gutters">
                                        <div class="col-auto">
                                            <h4><a href="#">{{ $review->user?->name }}</a></h4>
                                            <div class="ratings-container">
                                                <div class="ratings">
                                                    <div class="ratings-val"
                                                        style="width: {{ ($review->rating / 5) * 100 }}%;"></div>
                                                </div>
                                            </div>
                                            <span
                                                class="review-date">{{ $review->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="col">
                                            <div class="review-content">
                                                <p>{{ $review->comment }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
