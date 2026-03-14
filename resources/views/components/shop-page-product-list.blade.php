@foreach ($products as $product)
    <!-- start shop item -->
    @if ($product->category != null)
        @php
            $isInWishlist = false;
            $sessionWishlistData = session('wishlist') ?? [];
            if (auth()->user()) {
                $isInWishlist = $wishlist->contains('product_id', $product->product_id);
            } else {
                foreach ($sessionWishlistData as $key => $value) {
                    // dd($value['product_id'],$product->product_id);
                    if ($value['product_id'] == $product->product_id) {
                        $isInWishlist = true;
                    }
                }
            }
        @endphp
        {{-- @dd( session('wishlist')) --}}
        @php
            $mrp = $product->variations[0]->mrp ?? $product->base_mrp;
            $price = $product->variations[0]->price ?? $product->base_price;
            $percentOff = $mrp > 0 && $price > 0 ? round((($mrp - $price) / $mrp) * 100) : 0;
        @endphp

        <div class="col-md-4">
            <div class="container-full-sf">
                <div class="container-full-sf-in">
                    <a data-id="{{ $product->product_id }}" onclick="addTowishListClass(this)"
                        title="{{ $isInWishlist ? ' Added to wishlist' : ' Add to wishlist' }}"
                        class="wishlist-link-product  {{ $isInWishlist ? 'remove-from-wishlist liked' : 'add-to-wishlist' }}"><svg
                            class="wish-icon" title="Like Safebox SVG File" width="16" height="16"
                            viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                            </path>
                        </svg></a>
                    <a href="{{ route('single.product.view', $product->slug) }}">
                        <div class="prod-div">
                            <img class="product-img" src="{{ asset('storage/' . $product->product_image) }}">
                        </div>
                        <h3 class="product-title">{{ $product->product_name }}</h3>

                    </a>
                    <div class="prod-price">
                        @if ($price == 0)
                            <span class="new-price">
                                ₹{{ number_format($price == 0 ? $mrp : $price, 0, '.', ',') }}</span>
                        @else
                            <span class="old-price"> ₹{{ number_format($mrp, 0, '.', ',') }}</span>
                            <span class="new-price">
                                ₹{{ number_format($price == 0 ? $mrp : $price, 0, '.', ',') }}</span>
                        @endif
                    </div>
                </div>
                {{-- @dd($product->variation) --}}
                @if ($product->variation->stock > 0)
                    <div class="d-flex gap-10 align-items-center justify-content-center"
                        data-product-id="{{ $product->product_id }}">
                        <a class="add-to-cart-home add-to-cart">
                            <span>Add to cart</span>
                        </a>
                        <div class="product-details-quantity" wire:ignore>
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
                            <input type="number" class="form-control qty product_quantity" value="{{ $cartQty }}"
                                min="1" max="10" step="1" data-decimals="0" required>
                        </div>
                    </div>
                @else
                    <div class="d-flex gap-10 align-items-center justify-content-center">
                        <a class="add-to-cart-home disabled">
                            <span>Out of Stock</span>
                        </a>
                    </div>
                @endif

            </div>
        </div>
    @endif
@endforeach
