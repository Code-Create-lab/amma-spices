<?php

namespace App\Http\Livewire;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Variation;
use App\Models\Wishlist;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class ProductList extends Component
{

    public $products;
    public $categories;
    public $wishlist;
    public $quantity = 1;
    public $take = 5;

    public ?int $product_id = null;
    public ?int $variation_id = null;

    //  protected $listeners = ['addToCart'];

    public function mount($products)
    {
// dd(auth()->user());
        $user = auth()->user();
        if ($products) {
            $this->products = $products;
            // return;
        } else {

            $this->products =  Product::with(['variations', 'variations.variation_attributes', 'category'])
                ->where('is_deleted', 0)
                ->where('on_sale', 1)
                // ->take(5)
                ->get();
        }
        $this->wishlist = $user ? Wishlist::where('user_id', $user->id)->get() : collect();

        // dd( $this->wishlist);
        $this->categories = Category::where('is_deleted', 0)
            ->where('parent', 0)
            ->where('is_deleted', 0)
            ->get();
    }

    public function loadMore()
    {

        $this->take += 5;

        $this->products = Product::with(['variations', 'variations.variation_attributes', 'category'])
            ->where('is_deleted', 0)
            ->take($this->take)
            ->get();
    }



    public function render()
    {
        return view('livewire.product-list');
    }
}
