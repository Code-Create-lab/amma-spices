<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Attribute;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Response;

class ShopPageFilter extends Component
{
    use WithPagination;

    public $categories;
    public $filterAttributes = [];
    public $categoryIds = [];
    public $attributesData = [];
    public $sub_categories;
    public $wishlist;
    public $totalProductCount;
    public $selectedColor;

    public $initalPrice = 0;
    public $finalPrice = 0;


    public string $sort = 'name_asc';
    public string $search = '';
    public $slug;
    public $onSale;
    public $product_id;
    public $variation_id;
    public $quantity;
    public $getNewArrivalProducts;

    public $selectedColors = [];
    public $selectedSizes = [];
    public $activeFilterType = null; // Track which filter type is active

    // Separate properties for different attribute types
    public $colorAttributeIds = []; // Store color attribute IDs
    public $sizeAttributeIds = [];  // Store size attribute IDs


    public $colorAttributeArray = [];  // Store size attribute IDs
    public $sizeAttributeArray = [];  // Store size attribute IDs

    public $selectedCategories = [];
    public $selectedProductId = null;
    public $isProductListVisible = false;

    // protected $queryString = ['search', 'sort'];

    // // Reset pagination when search or sort changes
    // protected $updatesQueryString = ['search', 'sort'];
    protected $paginationTheme = 'bootstrap'; // or bootstrap

    public function mount($slug = null)
    {
        $user = auth()->user();

        if (
            request()->hasHeader('User-Agent') &&
            preg_match('/Mobile|Android|iPhone|iPod/i', request()->header('User-Agent')) &&
            !preg_match('/iPad/i', request()->header('User-Agent'))
        ) {
            $this->isProductListVisible = false;
        }else{
            $this->isProductListVisible = true;
        }


        $this->wishlist = $user ? Wishlist::where('user_id', $user->id)->get() : collect(session()->get('wishlist'));

        $this->categories = Category::with(['sub_categories'])
            ->where('level', 0)
            ->where('is_deleted', 0)
            ->get();

        // $this->categories = Category::with([
        //     'sub_categories' => function ($q) {
        //         $q->whereHas('products')
        //             ->withCount('products')
        //             ->with([
        //                 'sub_categories' => function ($q) {
        //                     $q->whereHas('products')
        //                         ->withCount('products');
        //                 }
        //             ]);
        //     }
        // ])
        //     ->whereHas('products') // parent category must have products OR children with products
        //     ->withCount('products')
        //     ->get();

        // dd($this->categories);

        $this->sub_categories = Category::where('level', '!=', 0)
            ->where('is_deleted', 0)
            ->where('is_deleted', 0)
            ->get();

        $this->slug = $slug;
        // $this->onSale = $onSale;


        // Match slug and pre-fill checked category
        if ($this->slug) {
            $category = Category::where('slug', $this->slug)->first();
            if ($category) {
                $this->categoryIds[] = $category->cat_id;
            }
        }

        // dd($this->categoryIds, $this->slug);
        $this->getNewArrivalProducts = Product::where("is_deleted", 0)->whereHas('variations', function ($query) {
            $query->where('is_deleted', 0); // or whatever your availability field is
            // OR if you check stock quantity:
            // $query->where('stock', '>', 0);
        })
            ->whereHas('category', function ($query) {
                $query->where('is_deleted', 0); // or whatever your availability field is
                // OR if you check stock quantity:
                // $query->where('stock', '>', 0);
            })->take(3)->get();

        $getMaxPrice = Product::max('base_mrp');

        // In your Livewire component
        $this->dispatch('save-to-localstorage', [
            'max_price' => $getMaxPrice
        ]);


        // if ($slug) {
        //     $this->sub_categories = Category::where('slug', $slug)->first()->sub_categories;
        //     // dd( $this->sub_categories->pluck('cat_id')->toArray());

        //     $this->categoryIds = $this->sub_categories->pluck('cat_id')->toArray();
        // }

        $this->filterAttributes = Attribute::with(['values'])->where('is_deleted', 0)->get();



        $getAllProducts = Product::query()
            ->where('is_deleted', 0)
            ->whereHas('variations', function ($query) {
                $query->where('is_deleted', 0); // or whatever your availability field is
                // OR if you check stock quantity:
                // $query->where('stock', '>', 0);
            })->whereHas('category', function ($query) {
                $query->where('is_deleted', 0); // or whatever your availability field is
                // OR if you check stock quantity:
                // $query->where('stock', '>', 0);
            })->get();

        foreach ($getAllProducts as $product) {

            foreach ($product->variations as $variation) {

                foreach ($variation->variation_attributes as $attributes) {


                    // dd($attributes->attribute);
                    $this->colorAttributeArray[]  = $attributes->attribute;
                    // $this->sizeAttributeArray[]  = $attributes->attribute;
                }
            }
        }

        // dd($this->colorAttributeArray);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSort()
    {
        $this->resetPage();
    }

    public function updatedCategoryIds()
    {
        $this->resetPage();
    }

    public function showProduct($productId, $categoryId = null)
    {
        $this->dispatch('show-product-detail-slider');
        $this->selectedProductId = $productId;
        if ($categoryId) {
            $this->selectedCategories = [$categoryId];
            $this->dispatch('category-selected', categoryId: $categoryId);
        }
    }



    #[On('priceFilter')]
    public function priceFilter(...$price)
    {

        // dd($price);
        $this->initalPrice =  (int) preg_replace('/[^0-9]/', '', $price[0][0]);
        $this->finalPrice = (int) preg_replace('/[^0-9]/', '', $price[0][1]);

        // dd(  $this->initalPrice ,  $this->finalPrice);
    }

    public function filter($color = null, $categoryId = [], $size = null)
    {


        // Handle category filtering (this works fine, don't change)
        if (!empty($categoryId)) {
            $this->activeFilterType = 'category';
            $this->categoryIds = is_array($categoryId) ? $categoryId : [$categoryId];
            // Category filtering doesn't affect other filters
        }

        // Handle color filtering
        if ($color !== null) {
            $this->activeFilterType = 'color';

            // if (in_array($color, $this->selectedColors)) {
            //     // Remove color if already selected
            //     $this->selectedColors = array_filter($this->selectedColors, fn($c) => $c != $color);
            // } else {
            // Add color to selection
            $this->selectedColors = [$color];
            // }

            // Clear size selection when color is selected
            $this->selectedSizes = [];
        }

        // Handle size filtering  
        if ($size !== null) {
            $this->activeFilterType = 'size';

            // if (in_array($size, $this->selectedSizes)) {
            //     // Remove size if already selected
            //     $this->selectedSizes = array_filter($this->selectedSizes, fn($s) => $s != $size);
            // } else {
            // Add size to selection
            $this->selectedSizes = [$size];
            // }
            // Clear color selection when size is selected
            $this->selectedColors = [];
        }
        // dd($this->selectedSizes,$this->selectedColors);
        //   dd($this->categoryIds, $this->selectedColors, $this->selectedSizes);
        // If no filters are selected, clear active filter type
        if (empty($this->selectedColors) && empty($this->selectedSizes)) {
            $this->activeFilterType = null;
        }
    }

    public function clearFilter()
    {

        $this->categoryIds = [];
        $this->attributesData = [];
        $this->selectedColors = [];
        $this->selectedCategories = [];
        $this->sort = '';
    }


    public function updatedSelectedCategories()
    {
        $this->resetPage();
    }

    public function toggleCategory($categoryId)
    {
        // Always keep only the latest selected category
        $this->selectedCategories = [$categoryId];
        $this->selectedProductId = null;
        $this->isProductListVisible = true;
        $this->resetPage();
        $this->dispatch('category-selected', categoryId: $categoryId);
    }


    public function render()
    {

        //  dd($this->attributesData);

        // dd((session()->get('wishlist')), $this->wishlist);
        // try {
        // dd($this->categoryIds);
        // dd($this->selectedCategories);
        $this->wishlist = collect(session()->get('wishlist'));
        $productsQuery = Product::query()
            ->where('is_deleted', 0)
            ->when(count($this->selectedCategories) > 0, function ($query) {
                $query->whereHas('category', function ($q) {
                    $q->whereIn('categories.cat_id', $this->selectedCategories);
                });
            })
            ->whereHas('variations', function ($query) {
                $query->where('is_deleted', 0); // or whatever your availability field is
                // OR if you check stock quantity:
                // $query->where('stock', '>', 0);
            })->whereHas('category', function ($query) {
                $query->where('is_deleted', 0); // or whatever your availability field is
                // OR if you check stock quantity:
                // $query->where('stock', '>', 0);
            });

        $this->totalProductCount = Product::where('is_deleted', 0)->count();
        // $categoryModel = new Category();
        // $getParentCat =  $categoryModel->whereIn('cat_id',  $this->categoryIds)->where('parent', 0)->pluck('cat_id')->toArray();
        // // 
        // if (count($getParentCat) > 0) {

        //     // dd($getParentCat,$this->categoryIds );
        //     $getSubCategories = $categoryModel->whereIn('parent',  $getParentCat)->pluck('cat_id')->toArray();
        //     if (count($getSubCategories) > 0) {

        //         $getSubCatIds = $categoryModel->whereIn('cat_id',  $getSubCategories)->pluck('cat_id')->toArray();
        //     } else {
        //         $getSubCatIds = $categoryModel->whereIn('cat_id',  $this->categoryIds)->pluck('cat_id')->toArray();
        //     }
        //     $getSubCatIds = array_merge($getSubCatIds, $getParentCat);

        //     //  dd($getSubCatIds,$this->categoryIds ,  $getSubCategories,$getParentCat);
        // } else {
        //     $get_sub_cat = $categoryModel->whereIn('cat_id',  $this->categoryIds)->pluck('parent');

        //     $getSubCatIds = $this->categoryIds;
        // }

        // // dd( $getSubCatIds, $this->categoryIds);
        // // dd($this->categoryIds, $productsQuery->whereIn('cat_id', $getSubCatIds )->get(), $getParentCat, $getSubCatIds);


        // if ($this->slug) {

        //     // // dd($this->onSale);
        //     // if($this->onSale){
        //     //       $productsQuery->where('on_sale', true);
        //     // }
        //     // dd($getSubCatIds);
        //     // dd( $getSubCatIds);
        //     $this->totalProductCount = Product::where('is_deleted', 0)->whereIn('cat_id', $getSubCatIds)->count();
        //     //  dd( $this->categoryIds, $getSubCatIds,  $this->totalProductCount, $getParentCat);

        // } else {

        //     $this->totalProductCount = Product::where('is_deleted', 0)->count();
        // }


        // if ($this->categoryIds) {

        //     // dd($getSubCatIds,$this->categoryIds ,  $getSubCatIds);
        //     $productsQuery->whereIn('cat_id', $getSubCatIds);
        // }


        // dd( $productsQuery->get(), $getSubCatIds);







        //  dd($this->activeFilterType);

        // 2. Attribute filters (only one type at a time)
        if ($this->activeFilterType === 'color' && !empty($this->selectedColors)) {
            // Filter by colors only
            $productsQuery = Product::query()->where("is_deleted", 0);
            $productsQuery->whereHas('variations', function ($variationQuery) {
                $variationQuery->whereHas('variation_attributes', function ($q) {
                    $q->whereIn('attribute_id', $this->selectedColors);
                });
            });
        } elseif ($this->activeFilterType === 'size' && !empty($this->selectedSizes)) {
            // Filter by sizes only
            $productsQuery = Product::query()->where("is_deleted", 0);
            $productsQuery->whereHas('variations', function ($variationQuery) {
                $variationQuery->whereHas('variation_attributes', function ($q) {
                    $q->whereIn('attribute_id', $this->selectedSizes);
                });
            });
        }




        if ($this->finalPrice != 0) {
            // dd( $this->finalPrice );


            // $productsQuery->whereHas('variations', function ($q) {
            //     $q->whereBetween('mrp', [ $this->initalPrice ,  $this->finalPrice ]);; // Filter variations having mrp = 500
            // });

            $productsQuery->where(function ($query) {
                $query->whereHas('variations', function ($q) {
                    $q->whereBetween('price', [$this->initalPrice, $this->finalPrice]);
                })
                    ->orWhereDoesntHave('variations');
            });
        }


        // dd($this->categoryIds, $getSubCatIds , $productsQuery->whereIn('cat_id', $getSubCatIds)->get(), empty($this->attributesData));

        // dd($productsQuery->get());

        // Sorting logic
        switch ($this->sort) {
            case 'date':
                $productsQuery->orderBy('created_at', 'desc');
                break;
            case 'price_low_high':
                $productsQuery->orderBy('base_price', 'asc');
                break;
            case 'price_high_low':
                $productsQuery->orderBy('base_price', 'desc');
                break;
            case 'name_asc':
                $productsQuery->orderBy('product_name', 'asc');
                break;
            default:
                $productsQuery->orderBy('product_name', 'asc');
                break;
        }


        $products = $productsQuery->orderBy('tags', 'desc')->paginate(12);



        return view('livewire.shop-page-filter', [
            'products' => $products,
            'categories' => $this->categories,
            'wishlist' => $this->wishlist,
            'attributes' => $this->filterAttributes,
        ]);
        // } catch (\Throwable $e) {
        //     // \Log::error('Livewire render error: ' . $e->getMessage());

        //     // Optionally set a flash message or return fallback view
        //     session()->flash('error', 'Something went wrong while loading products.');

        //     return view('livewire.shop-page-filter', [
        //         'products' => collect(), // empty
        //         'categories' => [],
        //         'wishlist' => [],
        //         'attributes' => [],
        //     ]);
        // }
    }



    public function addToWishlist($product_id)
    {


        // dd($product_id);
        // Assign incoming values to component properties
        $this->product_id = $product_id;


        $product_id = $product_id;
        // $variation = Variation::find($this->variation_id);
        $variation = Product::find($product_id)->variation;
        // dd( $variation);
        $this->variation_id  = $variation->id;
        if (!$variation || $variation->stock < $this->quantity) {
            return response()->json(['success' => false, 'message' => 'Out of stock']);
        }
        if (!auth()->check()) {

            // dd( session()->get('cart'));
            // dd( $this->product_id , $product_id['product_id']);
            $wishlist = session()->get('wishlist', []);
            $message = "";
            $key = $product_id;

            if (isset($wishlist[$key])) {
                // dd($wishlist,$key);
                unset($wishlist[$key]);

                $message = 'Product removed from wishlist';
                // $this->updateWishlist();
                $this->dispatch('updateWishlistNavCart');
                session()->put('wishlist', $wishlist);
                $wishlist_count = collect($wishlist)->count();
                return  $this->dispatch('toast', [
                    'type' => 'error',
                    'success' => true,
                    'message' => $message,
                ]);
            } else {
                $wishlist[$key] = [
                    'product_id' => $product_id,
                    'variation_id' => $this->variation_id
                ];
                $message = 'Product added to Wishlist successfully';
                dd($wishlist, $key, $message);
                // $this->updateWishlist();
                $this->dispatch('updateWishlistNavCart');
                session()->put('wishlist', $wishlist);
                $wishlist_count = collect($wishlist)->count();


                return  $this->dispatch('toast', [
                    'type' => 'success',
                    'success' => true,
                    'message' => $message,
                ]);
            }
            // $this->updateWishlist();
            // session()->put('wishlist', $wishlist);
            // $wishlist_count = collect($wishlist)->count();

            // dd($wishlist) ;
            //   $this->updateWishlist();
            //    dd(session()->get('wishlist'), $wishlist);





            // if(\Request::route()->getName() == 'shop_grid'){

            //     return redirect(route('shop.page.index'));
            // }else{

            // dd(\Request::route()->getName() );
            // $this->dispatch('toast', [
            //     'type' => 'success',
            //     'success' => true,
            //     'message' => $message,
            // ]);


            return $this->dispatch('updateWishlistNavCart'); // 🔥 emit global event
            // dd("asdsa");
            //  return redirect(request()->header('Referer'));
            // }

            // return   $this->dispatch('toast', [
            //             'type' => 'error',
            //             'success' => true,
            //             'message' => 'Please Login!',
            //         ]);

        }

        try {
            $wishlist = Wishlist::where('product_id', $this->product_id)->where('user_id', Auth::id())->first();

            // dd($wishlist,$this->product_id);
            if (empty($wishlist)) {
                $product = Product::where('product_id', $this->product_id)->first();
                Wishlist::create([
                    'user_id' => Auth::id(),
                    'quantity' => 1,
                    'product_id' => $this->product_id,
                    'store_id' => 1,
                    'product_name' => $product->product_name,
                    'description' => $product->description,
                    'price' => $product->base_price,
                    'mrp' => $product->base_mrp,
                    'product_image' => $product->product_image
                ]);
                $wishlist_count = Wishlist::where('user_id', Auth::id())->count();

                //  $this->updateWishlist();

                $this->dispatch('toast', [
                    'type' => 'success',
                    'success' => true,
                    'message' => 'Product Added To Wishlist',
                ]);


                Response::json([
                    'success' => true,
                    'message' => 'Product Added To Wishlist',
                    'count' => $wishlist_count
                ]);

                return $this->dispatch('updateWishlistNavCart'); // 🔥 emit global event
                //   return redirect(route('shop.page.index'));
            } else {

                $this->dispatch('toast', [
                    'type' => 'success',
                    'success' => true,
                    'message' => 'Product Already In Wishlist'
                ]);

                return Response::json([
                    'success' => false,
                    'message' => 'Product Already In Wishlist'
                ]);
            }
        } catch (Exception $e) {

            $this->dispatch('toast', [
                'type' => 'success',
                'success' => true,
                'message' => $e->getMessage()
            ]);

            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }



    public function addToCart($product_id)
    {

        // dd(auth()->check());

        // Assign incoming values to component properties
        $this->product_id = $product_id;
        $this->quantity =  1;

        $product_id = $product_id;
        // $variation = Variation::find($this->variation_id);
        $variation = Product::find($product_id)->variation;
        $product = Product::find($product_id);
        // dd($variation);
        // if ($variation == null) {
        //   return  $this->dispatch('toast', [
        //         'type' => 'error',
        //         'success' => true,
        //         'message' => "Product Variation is no available",
        //     ]);
        // }
        $this->variation_id  = $variation->id;
        if (!$variation || $variation->stock < $this->quantity) {
            return response()->json(['success' => false, 'message' => 'Out of stock']);
        }

        if (auth()->check()) {
            try {
                DB::beginTransaction();
                $userId = Auth::id();

                // Merge session cart on first cart action
                // $this->mergeSessionCartToDatabase();

                $cartItem = Cart::where('product_id', $product_id)
                    ->where('variation_id', $this->variation_id)
                    ->where('user_id', $userId)
                    ->first();

                if ($cartItem) {

                    if ($cartItem->quantity == $this->quantity) {

                        $message = 'Product already added in cart';
                    } else {

                        $cartItem->quantity = $this->quantity;
                        $cartItem->save();
                        $message = 'Product already added in cart';
                    }
                } else {
                    Cart::create([
                        'product_id' => $product_id,
                        'variation_id' => $this->variation_id,
                        'user_id' => $userId,
                        'quantity' => $this->quantity,
                    ]);
                    $message = 'Product added to cart successfully';
                }

                $cartItems = Cart::where('user_id', $userId)->with('variation')->get();
                // $cartTotal = $cartItems->sum(fn($item) => $item->quantity * $item->variation->price);

                $cartTotal = $cartItems->sum(fn($item) => $item->quantity * ($item->variation && $item->variation->price ? $item->variation->price : $item->base_price));


                // dd($cartTotal);

                DB::commit();

                $this->dispatch('toast', [
                    'type' => 'success',
                    'success' => true,
                    'message' => $message,
                ]);


                // Emit a global event
                $this->dispatch('cart-updated'); // 🔥 emit global event

                // dd("asdasd");


                response()->json([
                    'success' => true,
                    'message' => $message,
                    'cart_count' => $cartItems->count(),
                    'cart_total' => $cartTotal,
                ]);

                return redirect(route('shop.page.index'));
            } catch (Exception $e) {
                //  dd("asdasd", $e);
                DB::rollBack();
                $this->dispatch('toast', [
                    'type' => 'error',
                    'success' => true,
                    'message' => $e->getMessage(),
                ]);
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            // Guest user
            $cart = session()->get('cart', []);
            $key = $product_id . '-' . $this->variation_id;

            if (isset($cart[$key])) {
                $cart[$key]['quantity'] = $this->quantity;
                $message = 'Product already added in cart';
            } else {
                $cart[$key] = [
                    'product_id' => $product_id,
                    'variation_id' => $this->variation_id,
                    'quantity' => $this->quantity,
                    'price' => $variation->price ?? $product->base_price,
                ];
                $message = 'Product added to cart successfully';
            }

            session()->put('cart', $cart);

            // dd($cart);

            $cartTotal = collect($cart)->sum(fn($item) => ($item['quantity'] ?? 1) * $item['price']);
            // $cartTotal = collect($cart)->sum(fn($item) => ($item['quantity'] ?? 1) * ($item['variation']['price'] ?? $item['base_price']));
            // $cartTotal = $cartItems->sum(fn($item) => $item->quantity * ($item->variation && $item->variation->price ? $item->variation->price : $item->base_price));


            // Emit a global event
            $this->dispatch('cart-updated'); // 🔥 emit global event

            $this->dispatch('toast', [
                'type' => 'success',
                'success' => true,
                'message' => $message,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'cart_count' => count($cart),
                'cart_total' => $cartTotal,
            ]);
        }
    }
}
