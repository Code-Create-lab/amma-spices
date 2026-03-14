<?php

namespace App\View\Components;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\View\Component;

class ProductList extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $products;
    public $wishlist;

    public function __construct($products)
    {
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
    }


    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.product-list');
    }
}
