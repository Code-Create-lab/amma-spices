<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\RatingReview;
use Illuminate\Support\Facades\Auth;


class ProductDetails extends Component
{
    public $productId;
    public $product;
    public $response;
    public $wishlist;
    public $isInWishlist = false;
    public $total_reviews = 0;
    public $avg_rating = 0;
    public $top_reviews;
    public $related_products;
    public $quantity = 1;
    public $selectedAttributes = [];
    public $selectedVariationId = null;
    public $currentPrice;
    public $currentMrp;
    public $currentStock;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount($productId)
    {
        $this->productId = $productId;
        $this->loadProductData();
        $this->dispatch('init-product-carousel');
    }

    public function loadProductData()
    {
        // Load product with all relationships
        $this->product = Product::with([
            'category',
            'images',
            'variations.variation_attributes',
            // 'variations.attributeOptions',
            'reviews.user'
        ])->findOrFail($this->productId);

        // Format response similar to your original structure
        $this->response = [
            'id' => $this->product->product_id,
            'name' => $this->product->product_name,
            'slug' => $this->product->slug,
            'description' => $this->product->description,
            'info' => $this->product->info,
            'shipping' => $this->product->shipping ?? '',
            'base_price' => $this->product->base_price,
            'base_mrp' => $this->product->base_mrp,
            'category' => $this->product->category,
            'images' => $this->product->images,
            'variations' => $this->product->variations->map(function ($variation) {
                return [
                    'id' => $variation->id,
                    'price' => $variation->price,
                    'mrp' => $variation->mrp,
                    'stock' => $variation->stock,
                    'attributes' => $variation->variation_attributes->map(function ($attr) {
                        return [
                            'name' => $attr->name,
                            'value' => $attr->value,
                            'id' => $attr->id
                        ];
                    }),
                    'attributes_options' => $variation->attributeOptions
                ];
            }),
            'variation_attributes' => $this->getVariationAttributes()
        ];

        // Set initial prices
        $firstVariation = $this->product->variations->first();
        $this->currentPrice = $firstVariation->price ?? $this->product->base_price;
        $this->currentMrp = $firstVariation->mrp ?? $this->product->base_mrp;
        $this->currentStock = $firstVariation->stock ?? 0;

        // Load wishlist
        $this->loadWishlist();

        // Load reviews
        $this->loadReviews();

        // Load related products
        $this->loadRelatedProducts();
    }

    public function getVariationAttributes()
    {
        $attributes = [];

        foreach ($this->product->variations as $variation) {
            foreach ($variation->variation_attributes as $attribute) {
                $attrName = $attribute->name;

                if (!isset($attributes[$attrName])) {
                    $attributes[$attrName] = [];
                }

                // Check if this attribute value already exists
                $exists = false;
                foreach ($attributes[$attrName] as $existing) {
                    if ($existing['value'] === $attribute->value) {
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) {
                    $attributes[$attrName][] = [
                        'id' => $attribute->id,
                        'value' => $attribute->value,
                        'name' => $attribute->name
                    ];
                }
            }
        }

        return $attributes;
    }

    public function loadWishlist()
    {
        if (Auth::check()) {
            $this->wishlist = Wishlist::where('user_id', Auth::id())->get();
            $this->isInWishlist = $this->wishlist->contains('product_id', $this->productId);
        } else {
            $sessionWishlist = session('wishlist', []);
            foreach ($sessionWishlist as $item) {
                if ($item['product_id'] == $this->productId) {
                    $this->isInWishlist = true;
                    break;
                }
            }
        }
    }

    public function loadReviews()
    {
        $this->top_reviews = RatingReview::where('product_id', $this->productId)
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        $this->total_reviews = RatingReview::where('product_id', $this->productId)->count();
        $this->avg_rating = RatingReview::where('product_id', $this->productId)->avg('rating') ?? 0;
    }

    public function loadRelatedProducts()
    {
        $this->related_products = Product::where('cat_id', $this->product->cat_id)
            ->where('product_id', '!=', $this->productId)
            ->with(['variation', 'images'])
            ->take(8)
            ->get()
            ->map(function ($product) {
                $firstImage = $product->images->first();
                $firstVariation = $product->variation;

                return (object) [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'slug' => $product->slug,
                    'product_image' => $firstImage ? $firstImage->image : '',
                    'base_price' => $product->base_price,
                    'base_mrp' => $product->base_mrp,
                    'variation' => $firstVariation ? (object) [
                        'stock' => $firstVariation->stock
                    ] : null
                ];
            });
    }

    public function selectAttribute($attributeName, $attributeValue, $attributeId)
    {
        $this->selectedAttributes[$attributeName] = [
            'id' => $attributeId,
            'value' => strtolower(str_replace(' ', '', $attributeValue))
        ];

        $this->updateSelectedVariation();
    }

    public function updateSelectedVariation()
    {
        // Find matching variation based on selected attributes
        foreach ($this->product->variations as $variation) {
            $matches = true;

            foreach ($this->selectedAttributes as $attrName => $attrData) {
                $found = false;

                foreach ($variation->variation_attributes as $varAttr) {
                    $normalizedVarValue = strtolower(str_replace(' ', '', $varAttr->value));

                    if ($varAttr->name === $attrName && $normalizedVarValue === $attrData['value']) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $matches = false;
                    break;
                }
            }

            if ($matches && count($this->selectedAttributes) === $variation->variation_attributes->count()) {
                $this->selectedVariationId = $variation->id;
                $this->currentPrice = $variation->price;
                $this->currentMrp = $variation->mrp;
                $this->currentStock = $variation->stock;
                return;
            }
        }
    }

    public function incrementQuantity()
    {
        if ($this->quantity < 10) {
            $this->quantity++;
        }
    }

    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart()
    {
        // $variationId = $this->selectedVariationId ?? $this->product->variations->first()->id;

        $this->dispatch(
            'addToCart',
            product_id: $this->productId,
            quantity: $this->quantity
        );
    }

    public function toggleWishlist()
    {
        if (Auth::check()) {
            if ($this->isInWishlist) {
                Wishlist::where('user_id', Auth::id())
                    ->where('product_id', $this->productId)
                    ->delete();
                $this->isInWishlist = false;
            } else {
                Wishlist::create([
                    'user_id' => Auth::id(),
                    'product_id' => $this->productId
                ]);
                $this->isInWishlist = true;
            }
            $this->loadWishlist();
        } else {
            $sessionWishlist = session('wishlist', []);

            if ($this->isInWishlist) {
                $sessionWishlist = array_filter($sessionWishlist, function ($item) {
                    return $item['product_id'] != $this->productId;
                });
                $this->isInWishlist = false;
            } else {
                $sessionWishlist[] = ['product_id' => $this->productId];
                $this->isInWishlist = true;
            }

            session(['wishlist' => array_values($sessionWishlist)]);
        }

        $this->dispatch('wishlist-updated');
    }

    public function render()
    {
        return view('livewire.productdetails');
    }
}
